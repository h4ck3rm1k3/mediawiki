/*
 * Copyright 2004 Kate Turner
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy 
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is 
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in 
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * 
 * $Id$
 */
package org.wikimedia.lsearch;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.net.Socket;
import java.net.URLDecoder;
import java.net.URLEncoder;
import java.sql.DriverManager;
import java.text.MessageFormat;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.apache.lucene.analysis.Analyzer;
import org.apache.lucene.document.Document;
import org.apache.lucene.index.IndexReader;
import org.apache.lucene.index.Term;
import org.apache.lucene.queryParser.QueryParser;
import org.apache.lucene.search.FuzzyTermEnum;
import org.apache.lucene.search.Hits;
import org.apache.lucene.search.IndexSearcher;
import org.apache.lucene.search.Query;
import org.apache.lucene.search.Searcher;

/**
 * @author Kate Turner
 *
 */
public class SearchClientReader extends Thread {
	public static class State {
		Searcher searcher = null;
		Analyzer analyzer = null;
		QueryParser parser = null;
		IndexReader reader = null;		
		TitlePrefixMatcher matcher = null;
		String indexpath;
		
		public State(String dbname) {
			try {
				String[] urlargs = {dbname};
				MessageFormat form = new MessageFormat(MWDaemon.indexPath);
				indexpath = form.format(urlargs);

				analyzer = new EnglishAnalyzer();
				parser = new QueryParser("contents", analyzer);
				reader = IndexReader.open(indexpath);
				searcher = new IndexSearcher(reader);
				System.out.println("Reading title index...");
				matcher = new TitlePrefixMatcher(dbname, MWDaemon.getDBConn(dbname));
			} catch (IOException e) {
				System.err.println("Could not initialise search reader: " 
						+ e.getMessage());
				System.exit(0);
			}
		}
	}
	
	Socket client;
	String rawsearchterm;
	String searchterm;
	BufferedReader istrm;
	BufferedWriter ostrm;
	String what;
	public static HashMap states;
	String dbname;
	State state;
	
	// lucene special chars: + - && || ! ( ) { } [ ] ^ " ~ * ? : \
	static String[] specialChars = {
			"\\+", "-", "&&", "\\|\\|", "!", "\\(", "\\)", "\\{", "\\}", "\\[", "\\]",
			"\\^", "\"", "~", "\\*", "\\?", ":", "\\\\"
			//"\\(", "\\)"
	};

	public static void init() {
		states = new HashMap();
		for (int i = 0; i < MWDaemon.dbnames.length; ++i) {
			State s = new State(MWDaemon.dbnames[i]);
			states.put(MWDaemon.dbnames[i], s);
		}
	}
	
	public SearchClientReader(Socket newclient) {
		this.client = newclient;
	}
	
	public void run() {
		try {
			istrm = new BufferedReader(new InputStreamReader(client.getInputStream()));
			ostrm = new BufferedWriter(new OutputStreamWriter(client.getOutputStream()));
			dbname = istrm.readLine();
			state = (State) states.get(dbname);
			if (state == null) {
				istrm.close();
				ostrm.close();
				return;
			}
			what = istrm.readLine();
			rawsearchterm = istrm.readLine();
			rawsearchterm = URLDecoder.decode(rawsearchterm, "UTF-8");
			//for (int i = 0; i < specialChars.length; ++i)
			//	rawsearchterm = rawsearchterm.replaceAll(specialChars[i], 
			//			"\\" + specialChars[i]);
			//String escaped = "";
			//for (int i = 0; i < rawsearchterm.length(); ++i)
			//	if ("")
			//	escaped += "\\" + rawsearchterm.charAt(i);
			String escaped = rawsearchterm;
			
			if (what.equals("TITLEMATCH")) {
				doTitleMatches(escaped);
				return;
			} else if (what.equals("TITLEPREFIX")) {
				doTitlePrefix(rawsearchterm);
				return;
			}
			
			searchterm = "title:(" + escaped + ")^4 " + escaped + "";
			
			//System.out.println("Query: " + searchterm);
			Query query = state.parser.parse(searchterm);
			//System.out.println("Parsed: [" + query.toString() + "]");
	        Hits hits = state.searcher.search(query);
	        int numhits = hits.length();
	        //System.out.println(numhits + " hits");
	        ostrm.write(numhits + "\n");
	        int i = 0;
	        while (i < numhits) {
	        	Document doc = hits.doc(i);
	        	float score = hits.score(i);
	        	String namespace = doc.get("namespace");
	        	String title = doc.get("title");
	        	ostrm.write(score + " " + namespace + " " + 
	        			title.replaceAll(" ", "_") + "\n");
	        	++i;
	        }
	        if (numhits == 0) {
	        	String spelfix = makeSpelFix(rawsearchterm);
	        	ostrm.write(URLEncoder.encode(spelfix, "UTF-8") + "\n");
	        }
	        ostrm.flush();
		} catch (IOException e) {
		} catch (Exception e) {
			System.out.println("Unexpected exception: " + e.getMessage());
			e.printStackTrace();
		} finally {
			try {
				istrm.close();
				ostrm.flush();
				ostrm.close();
			} catch (IOException e) {}
		}
	}
	
	void doTitleMatches(String term) {
		try {
			String terms[] = term.split(" +");
			term = "";
			for (int i = 0; i < terms.length; ++i) {
				term += terms[i] + "~ ";
			}
			searchterm = "title:(" + term + ")";
			
			//System.out.println("Query: " + searchterm);
			Query query = state.parser.parse(searchterm);
			//System.out.println("Parsed: [" + query.toString() + "]");
			Hits hits = state.searcher.search(query);
			int numhits = hits.length();
			//System.out.println(numhits + " hits");
			int i = 0;
			while (i < numhits && i < 10) {
				Document doc = hits.doc(i);
				float score = hits.score(i);
				String namespace = doc.get("namespace");
				String title = doc.get("title");
				ostrm.write(score + " " + namespace + " " + 
						title.replaceAll(" ", "_") + "\n");
				++i;
			}
			ostrm.flush();
		} catch (IOException e) {
		} catch (Exception e) {
			System.out.println("Unexpected exception: " + e.getMessage());
			e.printStackTrace();
		} finally {
			try {
				istrm.close();
				ostrm.flush();
				ostrm.close();
			} catch (IOException e) {}
		}
	}

	void doTitlePrefix(String term) {
		//System.out.println("Query: ["+term+"]");
		List matches = state.matcher.getMatches(TitlePrefixMatcher.stripTitle(term));
		Iterator i;
		for (i = matches.iterator(); i.hasNext();) {
			Title t = (Title) i.next();
			try {
				ostrm.write("0 " + t.namespace + " " + 
						t.title.replaceAll(" ", "_") + "\n");
			} catch (IOException e) { 
				System.out.println("IO error: " + e.getMessage());
				break; 
			}
		}
		try {
			ostrm.flush();
			ostrm.close();
			istrm.close();
		} catch (IOException e) {}
	}

	String makeSpelFix(String query) {
		try {
			boolean anysuggest = false;
			String[] terms = query.split(" +");
			String ret = "";
			//System.out.println("spelcheck: [" + query + "]");
			for (int i = 0; i < terms.length; ++i) {
				//System.out.println("trying [" + terms[i] + "]");
				String bestmatch = terms[i];
				double bestscore = -1;
				FuzzyTermEnum enum = new FuzzyTermEnum(state.reader, 
						new Term("contents", terms[i]), 0.5f, 1);
				while (enum.next()) {
					Term term = enum.term();
					int score = editDistance(terms[i], term.text(), terms[i].length(),
							term.text().length());
					int weight = state.reader.docFreq(term);
					double fscore = (score*2) * 1/Math.sqrt(weight);
					//Query q = new TermQuery(term);
					//Hits h = searcher.search(q);
					//System.out.println("match: ["+term.text()+"] score " + score
					//		+ " weight " + weight + " = " + fscore);
					if (fscore > 4)
						continue;
					if (bestscore < 0 || fscore < bestscore) {
						bestscore = fscore;
						bestmatch = term.text();
						anysuggest = true;
					}
				}
				ret += bestmatch + " ";
			}
			if (!anysuggest) return "";
			return ret;
		} catch (IOException e) {
			return "";
		}
	}
	
	// Taken from the lucene source
	private int e[][] = new int[1][1];

	private static final int min(int a, int b, int c) {
        int t = (a < b) ? a : b;
        return (t < c) ? t : c;
    }

    private final int editDistance(String s, String t, int n, int m) {
        if (e.length <= n || e[0].length <= m) {
            e = new int[Math.max(e.length, n+1)][Math.max(e[0].length, m+1)];
        }
        int d[][] = e; // matrix
        int i; // iterates through s
        int j; // iterates through t
        char s_i; // ith character of s

        if (n == 0) return m;
        if (m == 0) return n;

        // init matrix d
        for (i = 0; i <= n; i++) d[i][0] = i;
        for (j = 0; j <= m; j++) d[0][j] = j;

        // start computing edit distance
        for (i = 1; i <= n; i++) {
            s_i = s.charAt(i - 1);
            for (j = 1; j <= m; j++) {
                if (s_i != t.charAt(j-1))
                    d[i][j] = min(d[i-1][j], d[i][j-1], d[i-1][j-1])+1;
                else d[i][j] = min(d[i-1][j]+1, d[i][j-1]+1, d[i-1][j-1]);
            }
        }

        // we got the result!
        return d[n][m];
    }
}
