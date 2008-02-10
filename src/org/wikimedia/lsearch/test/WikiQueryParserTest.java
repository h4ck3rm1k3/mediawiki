package org.wikimedia.lsearch.test;

import java.util.HashSet;

import org.apache.lucene.analysis.Analyzer;
import org.apache.lucene.search.Query;
import org.wikimedia.lsearch.analyzers.Analyzers;
import org.wikimedia.lsearch.analyzers.FieldBuilder;
import org.wikimedia.lsearch.analyzers.FieldNameFactory;
import org.wikimedia.lsearch.analyzers.WikiQueryParser;
import org.wikimedia.lsearch.analyzers.WikiQueryParser.NamespacePolicy;
import org.wikimedia.lsearch.config.Configuration;
import org.wikimedia.lsearch.config.IndexId;
import org.wikimedia.lsearch.index.WikiIndexModifier;

import junit.framework.TestCase;

public class WikiQueryParserTest extends TestCase {
	public void testParser() {
		Configuration.setConfigFile(System.getProperty("user.dir")+"/test-data/mwsearch.conf.test");
		Configuration.open();
		WikiQueryParser.TITLE_BOOST = 2;
		WikiQueryParser.ALT_TITLE_BOOST = 6;
		WikiQueryParser.KEYWORD_BOOST = 0.05f;
		WikiQueryParser.CONTENTS_BOOST = 1;
		IndexId enwiki = IndexId.get("enwiki");
		FieldBuilder.BuilderSet bs = new FieldBuilder(IndexId.get("enwiki")).getBuilder();
		Analyzer analyzer = Analyzers.getSearcherAnalyzer(enwiki);
		FieldNameFactory ff = new FieldNameFactory();
		
		try{
			WikiQueryParser parser = new WikiQueryParser("contents","0",analyzer,bs,NamespacePolicy.IGNORE);
			Query q;
			
			/* =================== BASICS ================= */ 
			q = parser.parseRaw("1991");
			assertEquals("contents:1991",q.toString());
			
			q = parser.parseRaw("\"eggs and bacon\" OR milk");
			assertEquals("contents:\"eggs and bacon\" contents:milk",q.toString());

			q = parser.parseRaw("+eggs milk -something");
			assertEquals("+(contents:eggs contents:egg^0.5) +contents:milk -contents:something",q.toString());			

			q = parser.parseRaw("eggs AND milk");
			assertEquals("+(contents:eggs contents:egg^0.5) +contents:milk",q.toString());

			q = parser.parseRaw("+egg incategory:breakfast");
			assertEquals("+contents:egg +category:breakfast",q.toString());
			
			q = parser.parseRaw("+egg incategory:\"two_words\"");
			assertEquals("+contents:egg +category:\"two words\"",q.toString());
			
			q = parser.parseRaw("+egg incategory:\"two words\"");
			assertEquals("+contents:egg +category:\"two words\"",q.toString());

			q = parser.parseRaw("incategory:(help AND pleh)");
			assertEquals("+category:help +category:pleh",q.toString());

			q = parser.parseRaw("incategory:(help AND (pleh -ping))");
			assertEquals("+category:help +(+category:pleh -category:ping)",q.toString());

			q = parser.parseRaw("(\"something is\" OR \"something else\") AND \"very important\"");
			assertEquals("+(contents:\"something is\" contents:\"something else\") +contents:\"very important\"",q.toString());

			q = parser.parseRaw("šđčćždzñ");
			assertEquals("contents:sđcczdzn contents:sđcczdznh",q.toString());
			
			/* =================== FULL QUERIES ================= */
			
			q = parser.parse("simple query",new WikiQueryParser.ParsingOptions(true));
			assertEquals("(+(contents:simple contents:simpl^0.5) +(contents:query contents:queri^0.5)) ((+title:simple^2.0 +title:query^2.0) (+(stemtitle:simple^0.8 stemtitle:simpl^0.32000002) +(stemtitle:query^0.8 stemtitle:queri^0.32000002)))",q.toString());
			
			q = parser.parse("simple -query",new WikiQueryParser.ParsingOptions(true));
			assertEquals("(+(contents:simple contents:simpl^0.5) -contents:query) ((+title:simple^2.0 -title:query^2.0) (+(stemtitle:simple^0.8 stemtitle:simpl^0.32000002) -stemtitle:query^0.8)) -(contents:query title:query^2.0 stemtitle:query^0.8)",q.toString());
			
			

		} catch(Exception e){
			e.printStackTrace();
		}
	}
}
