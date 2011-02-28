﻿/**
 * InScript regular expression rules table for Kannada script
 * According to CDAC's "Enhanced InScript Keyboard Layout 5.2"
 * @author Junaid P V ([[user:Junaidpv]])
 * @date 2011-02-27
 * License: GPLv3, CC-BY-SA 3.0
 */
 // Normal
 var rules = [
['x', '', '\u0C82'],
['_', '', '\u0C83'],
['D', '', '\u0C85'],
['E', '', '\u0C86'],
['F', '', '\u0C87'],
['R', '', '\u0C88'],
['G', '', '\u0C89'],
['T', '', '\u0C8A'],
['\\+', '', '\u0C8B'],
['Z', '', '\u0C8E'],
['S', '', '\u0C8F'],
['W', '', '\u0C90'],
['~', '', '\u0C92'],
['A', '', '\u0C93'],
['Q', '', '\u0C94'],
['k', '', '\u0C95'],
['K', '', '\u0C96'],
['i', '', '\u0C97'],
['I', '', '\u0C98'],
['U', '', '\u0C99'],
[';', '', '\u0C9A'],
['\\:', '', '\u0C9B'],
['p', '', '\u0C9C'],
['P', '', '\u0C9D'],
['\\}', '', '\u0C9E'],
["'", '', '\u0C9F'],
['"', '', '\u0CA0'],
['\\[', '', '\u0CA1'],
['\\{', '', '\u0CA2'],
['C', '', '\u0CA3'],
['l', '', '\u0CA4'],
['L', '', '\u0CA5'],
['o', '', '\u0CA6'],
['O', '', '\u0CA7'],
['v', '', '\u0CA8'],
['h', '', '\u0CAA'],
['H', '', '\u0CAB'],
['y', '', '\u0CAC'],
['Y', '', '\u0CAD'],
['c', '', '\u0CAE'],
['/', '', '\u0CAF'],
['j', '', '\u0CB0'],
['J', '', '\u0CB1'],
['n', '', '\u0CB2'],
['N', '', '\u0CB3'],
['b', '', '\u0CB5'],
['M', '', '\u0CB6'],
[',', '', '\u0CB7'],
['m', '', '\u0CB8'],
['u', '', '\u0CB9'],
['\\]', '', '\u0CBC'],
['e', '', '\u0CBE'],
['f', '', '\u0CBF'],
['r', '', '\u0CC0'],
['g', '', '\u0CC1'],
['t', '', '\u0CC2'],
['\\=', '', '\u0CC3'],
['z', '', '\u0CC6'],
['s', '', '\u0CC7'],
['w', '', '\u0CC8'],
['`', '', '\u0CCA'],
['a', '', '\u0CCB'],
['q', '', '\u0CCC'],
['d', '', '\u0CCD'],
['t', '', '\u0CC2'],
['0', '', '\u0CE6'],
['1', '', '\u0CE7'],
['2', '', '\u0CE8'],
['3', '', '\u0CE9'],
['4', '', '\u0CEA'],
['5', '', '\u0CEB'],
['6', '', '\u0CEC'],
['7', '', '\u0CED'],
['8', '', '\u0CEE'],
['9', '', '\u0CEF'],
['\\#', '', '\u0CCD\u0CB0'],
['\\%', '', '\u0C9C\u0CCD\u0C9E'],
['\\^', '', '\u0CA4\u0CCD\u0CB0'],
['\\&', '', '\u0C95\u0CCD\u0CB0'],
['\\(', '', '\u200D'],
['\\)', '', '\u200C']
];
// Extended
var rules_x = [
['F', '', '\uC88C'],
['\\>', '', '\u0CBD'],
['\\=', '', '\u0CC4'],
['H', '', '\u0CDE'],
['\\+', '', '\u0CE0'],
['R', '', '\u0CE1'],
['f', '', '\u0CE2'],
['r', '', '\u0CE3'],
['\\>', '', '\u0CE4'],
['\\.', '', '\u0CE5'],
['u', '', '\u0CF1'],
['j', '', '\u0CF2'],
['\\$', '', '\u20B9']
];

jQuery.narayam.addScheme( 'kn-inscript', {
    'namemsg': 'narayam-kn-inscript',
    'extended_keyboard': true,
    'lookbackLength': 0,
    'keyBufferLength': 0,
    'rules': rules,
    'rules_x': rules_x
} ); 