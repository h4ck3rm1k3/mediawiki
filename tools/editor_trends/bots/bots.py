#!/usr/bin/python
# -*- coding: utf-8 -*-
'''
Copyright (C) 2010 by Diederik van Liere (dvanliere@gmail.com)
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License version 2
as published by the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details, at
http://www.fsf.org/licenses/gpl.html
'''

__author__ = '''\n'''.join(['Diederik van Liere (dvanliere@gmail.com)', ])
__author__email = 'dvanliere at gmail dot com'
__date__ = '2010-11-25'
__version__ = '0.1'


import os
import cStringIO
import multiprocessing
import xml.etree.cElementTree as cElementTree
import sys
from Queue import Empty
sys.path.append('..')

import configuration
settings = configuration.Settings()
from wikitree import xml
from database import db
from utils import utils
from utils import process_constructor as pc
from etl import models
import models as botmodels

import cProfile

try:
    import psyco
    psyco.full()
except ImportError:
    pass


def read_bots_csv_file(location, filename, encoding, manager=False):
    '''
    Constructs a dictionary from Bots.csv
    key is language
    value is a list of bot names
    '''
    if manager:
        bot_dict = manager.dict()
    else:
        bot_dict = dict()
    for line in utils.read_data_from_csv(location, filename, encoding):
        line = utils.clean_string(line)
        language, bots = line.split(',')
        bots = bots.split('|')
        for bot in bots:
            if bot not in bot_dict:
                b = botmodels.Bot(bot)
                b.id = None
            else:
                b = bot_dict[bot]
            b.projects.append(language)
            bot_dict[bot] = b
    return bot_dict


def retrieve_bots():
    '''
    Loader function to retrieve list of id's of known Wikipedia bots. 
    '''
    ids = {}
    mongo = db.init_mongo_db('bots')
    bots = mongo['ids']
    cursor = bots.find()
    for bot in cursor:
        if bot['verified'] == 'True':
            ids[bot['id']] = bot['name']
    return ids


def store_bots():
    '''
    This file reads the results from the lookup_bot_userid function and stores
    it in a MongoDB collection. 
    '''
    keys = ['name', 'verified', 'projects']
    bots = utils.create_dict_from_csv_file(settings.csv_location, 'bots_ids.csv', settings.encoding, keys)
    mongo = db.init_mongo_db('bots')
    collection = mongo['ids']
    db.remove_documents_from_mongo_db(collection, None)

    for id in bots:
        bot = bots[id]
        data = dict([(k, bot[k]) for k in keys])
        data['id'] = id
        #{'id': int(id), 'name': name, 'verified': verified, 'projects': projects}
        collection.insert(data)

    print 'Stored %s bots' % collection.count()


def convert_object_to_dict(obj, exclude=[]):
    '''
    @obj is an arbitray object where the properties need to be translated to
    keys and values to ease writing to a csv file. 
    '''
    d = {}
    for o in obj:
        bot = obj[o]
        d[o] = {}
        for kw in bot.__dict__.keys():
            if kw not in exclude:
                d[o][kw] = getattr(bot, kw)
    return d


def write_bot_list_to_csv(bots):
    fh = utils.create_txt_filehandle(settings.csv_location, 'bots_ids.csv', 'w', settings.encoding)
    bot_dict = convert_object_to_dict(bots, exclude=['time', 'written'])
    keys = ['id', 'name', 'verified', 'projects']
    for bot in bot_dict:
        bot = bot_dict[bot]
        utils.write_dict_to_csv(bot, fh, keys, write_key=False, newline=True)
    fh.close()


def lookup_bot_userid(xml_nodes, bots):
    '''
    This function is used to find the id's belonging to the different bots that
    are patrolling the Wikipedia sites.
    @xml_nodes is a list of xml elements that need to be parsed
    @bots is a dictionary containing the names of the bots to lookup
    '''
    revisions = xml_nodes.findall('revision')
    for revision in revisions:
        contributor = xml.retrieve_xml_node(revision, 'contributor')
        username = contributor.find('username')
        if username == None or username.text == None:
            continue
        else:
            username = username.text #encode(settings.encoding)
        name = username.lower()

        #print username.encode('utf-8')
        if (username in bots and bots[username].verified == True) or name.find('bot') > -1:
            bot = bots.get(username, botmodels.Bot(username, verified=False))
            id = contributor.find('id').text
            bot.id = id
            bot.name = username
            timestamp = revision.find('timestamp').text
            if timestamp != None:
                timestamp = utils.convert_timestamp_to_datetime_naive(timestamp)
                bot.time[str(timestamp.year)].append(timestamp)

            bots[username] = bot
    return bots

    #bot = bots.get('PseudoBot')
    #bot.hours_active()
    #bot.avg_lag_between_edits()


def bot_launcher(language_code, project, single=False, manager=False):
    '''
    This function sets the stage to launch bot id detection and collecting data
    to discover new bots. 
    '''
    utils.delete_file(settings.csv_location, 'bots_ids.csv')
    location = os.path.join(settings.input_location, language_code, project)
    input = os.path.join(location, 'chunks')

    files = utils.retrieve_file_list(input, 'xml', mask=None)
    input_queue = pc.load_queue(files, poison_pill=True)
    tasks = multiprocessing.JoinableQueue()
    mgr = multiprocessing.Manager()
    #lock = mgr.Lock()
    if manager:
        manager = mgr
    bots = read_bots_csv_file(settings.csv_location, 'Bots.csv', settings.encoding, manager=manager)

    for file in files:
        tasks.put(models.XMLFile(input, settings.csv_location, file, None, lookup_bot_userid))

    tracker = {}
    if single:
        while True:
            try:
                print '%s files left in the queue...' % tasks.qsize()
                task = tasks.get(block=False)
                bots = task(bots)
            except Empty:
                break
    else:
        bot_launcher_multi(tasks)

    utils.store_object(bots, settings.binary_location, 'bots.bin')
    write_bot_list_to_csv(bots)
    bot_training_dataset(bots)
    store_bots()
    if bots != {}:
        print 'The script was unable to retrieve the user id\s for the following %s bots:\n' % len(bots)
        keys = bots.keys()
        for key in keys:
            try:
                print '%s' % key.encode(settings.encoding)
            except:
                pass




def bot_training_dataset(bots):
    fh = utils.create_txt_filehandle(settings.csv_location, 'training_bots.csv', 'w', settings.encoding)
    keys = bots.keys()
    for key in keys:
        bot = bots.get(key)
        bot.hours_active()
        bot.avg_lag_between_edits()
        bot.write_training_dataset(fh)

    fh.close()


def bot_launcher_multi(tasks):
    '''
    This is the launcher that uses multiprocesses. 
    '''
    consumers = [models.XMLFileConsumer(tasks, None) for i in xrange(settings.number_of_processes)]
    for x in xrange(settings.number_of_processes):
        tasks.put(None)

    for w in consumers:
        w.start()

    tasks.join()

def debug_bots_dict():
    bots = utils.load_object(settings.binary_location, 'bots.bin')
    for bot in bots:
        bot = bots[bot]
        edits = sum([len(bot.time[t]) for t in bot.time])
        print bot.name, bot.verified, edits
    print 'done'
    return bots

if __name__ == '__main__':
    language_code = 'en'
    project = 'wiki'
    store_bots()
    #bots = debug_bots_dict()
    #write_bot_list_to_csv(bots)
    #bot_launcher(language_code, project, single=True)
    #cProfile.run(bot_launcher(language_code, project, single=True), 'profile')
