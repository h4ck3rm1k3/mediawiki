/**
 * 
 */
package de.brightbyte.wikiword.analyzer.extractor;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

import de.brightbyte.wikiword.analyzer.WikiPage;

public class CategoryPatternParameterExtractor extends AbstractPatternParameterExtractor  {
	public CategoryPatternParameterExtractor(String pattern, String replacement, int flags, String property) {
		this(Pattern.compile(pattern, flags), replacement, property);
	}

	public CategoryPatternParameterExtractor(Pattern pattern, String replacement, String property) {
		this(pattern.matcher(""), replacement, property);
	}

	public CategoryPatternParameterExtractor(Matcher matcher, String replacement, String property) {
		super(matcher, replacement, property);
	}

	@Override
	protected Iterable<? extends CharSequence> getPageStrings(WikiPage page) {
		return page.getCategories();
	}
}