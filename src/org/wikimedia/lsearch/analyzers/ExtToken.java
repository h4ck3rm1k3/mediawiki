package org.wikimedia.lsearch.analyzers;

import java.awt.PageAttributes.OriginType;
import java.io.IOException;
import java.io.Serializable;
import java.io.UnsupportedEncodingException;
import java.util.ArrayList;
import java.util.HashMap;

import org.apache.log4j.Logger;
import org.apache.lucene.analysis.Token;
import org.apache.lucene.analysis.TokenStream;
import org.wikimedia.lsearch.util.Buffer;
import org.wikimedia.lsearch.util.UnicodeDecomposer;

/**
 * Extended information about the token, used for highlighting
 * 
 * @author rainman
 *
 */
public class ExtToken extends Token implements Serializable {
	static Logger log = Logger.getLogger(ExtToken.class);
	/** position within the text */
	public enum Position { NORMAL, FIRST_SECTION, TEMPLATE, IMAGE_CAT_IW, EXT_LINK, HEADING, REFERENCE, BULLETINS };
	/** type of token */
	public enum Type { TEXT, GLUE, SENTENCE_BREAK, MINOR_BREAK, URL };
	protected Position pos = Position.NORMAL;
	protected Type type = Type.TEXT;
	/** if the token text is different, this will hold the original text for reconstruction */
	protected String original = null;
	protected String inCase = null;

	public ExtToken(){
		super("",0,0);
	}
	
	public ExtToken(String text, int start, int end) {
		super(text, start, end);
	}

	public ExtToken(String text, int start, int end, String typ) {
		super(text, start, end, typ);
	}
	
	public ExtToken(String text, int start, int end, Type type, Position pos) {
		super(text, start, end);
		this.type = type;
		this.pos = pos;
	}
	
	public ExtToken(String text, int start, int end, Type type, Position pos, String original, String inCase) {
		super(text, start, end);
		this.type = type;
		this.pos = pos;
		this.original = original;
		this.inCase = inCase;
	}
	
	public ExtToken(String text, int start, int end, String typ, Type type, Position pos) {
		super(text, start, end, typ);
		this.type = type;
		this.pos = pos;
	}
	
	public ExtToken(String text, int start, int end, String typ, Type type, Position pos, String original) {
		super(text, start, end, typ);
		this.type = type;
		this.pos = pos;
		this.original = original;
	}
	
	public Position getPosition() {
		return pos;
	}

	public void setPosition(Position pos) {
		this.pos = pos;
	}

	public Type getType() {
		return type;
	}

	public void setType(Type type) {
		this.type = type;
	}

	public String getOriginal() {
		return original;
	}

	public void setOriginal(String original) {
		this.original = original;
	}
	
	public String getInCase(){
		return inCase;
	}
	
	public void setInCase(String inCase){
		this.inCase = inCase;
	}
	
	/** get text, original if available, otherwise termtext */
	public String getText(){
		if(original != null)
			return original;
		else
			return termText();
	}
	
	public String toString(){
		return "\""+termText()+"\",t="+type+",p="+pos+(original!=null? ",o={"+original+"}" : "")+",i="+getPositionIncrement();
	}
	
	/**
	 * 
	 * Serialization 
	 * 
	 * @author rainman
	 *
	 */
	
	/** Serialize a token stream for highlight storage */
	public static byte[] serialize(TokenStream tokens) throws IOException{
		Buffer b = new Buffer();
		Position pos = Position.NORMAL, lastPos = null;
		boolean wroteFirst = false;
		
		Token tt = null;
		while((tt = tokens.next()) != null){
			lastPos = pos;
			
			ExtToken t = null;
			if(tt instanceof ExtToken)
				t = (ExtToken)tt;
			else{
				t = new ExtToken(tt.termText(),tt.startOffset(),tt.endOffset(),Type.TEXT,lastPos,null,null);
				t.setPositionIncrement(tt.getPositionIncrement());
			}			
			pos = t.pos;
			
			//System.out.println("TOKEN: "+t);
			
			if(!wroteFirst){
				if(t.type == Type.GLUE && t.termText().equals(" "))
					continue; // ignore first spaces				
				//b.write((byte)t.type.ordinal());
				b.write((byte)t.pos.ordinal());
				wroteFirst = true;
			}
			
			// control 5: URLs
			if(t.type == Type.URL){
				b.writeControl(5);
				b.writeStringWithLength(t.termText());
			} else {				
				if(t.getPositionIncrement() != 0){
					if(t.inCase != null)
						b.writeString(t.inCase);
					else
						b.writeString(t.termText());
				}
			}
			
			if(t.getPositionIncrement() > 0 && t.original != null){
				String w  = t.termText();
				if(t.original.equals(w.substring(0,1).toUpperCase()+w.substring(1))){
					// control 6: original is title case
					b.writeControl(6);
				} else if(t.original.equals(w.toUpperCase())){
					// control 7: original is upper case
					b.writeControl(7);
				} else{
					// control 1: original word
					b.writeControl(1);
					b.writeStringWithLength(t.original);
				}
			}
			// control 2: alias
			if(t.getPositionIncrement() == 0){
				b.writeControl(2);
				b.writeStringWithLength(t.termText());
			}
			// control 3: change in position
			if(lastPos != pos){
				b.writeControl(3);
				b.write((byte)pos.ordinal());
			}
			// control 4: sentence break
			if(t.type == Type.SENTENCE_BREAK){
				b.writeControl(4);
			}
		}		
		return b.getBytes();
	}
	
	
	private static int findControl(byte[] b, int cur){
		for(int i=cur;i<b.length;i++){
			if(b[i] == (byte)0xff)
				return i;
		}
		return b.length;
	}

	/**
	 * 
	 * Deserialization stuff
	 * 
	 */
	
	protected static UnicodeDecomposer decomposer = null;
	
	enum ParseType { TEXT, NUMBER, GAP};
	
	/** c - current char, prev - previous char */
	private static boolean isText(char c, char prev){
		return Character.isLetterOrDigit(c) || (c=='\'' && Character.isLetter(prev)) || decomposer.isCombiningChar(c); 
	}
	
	/** Get a single token from a string, beginning at position inx */
	private static ExtToken getToken(String s, int inx){
		Type type;
		char c = s.charAt(inx);		 
		if(isText(c,'\0'))
			type = Type.TEXT;
		else if(FastWikiTokenizerEngine.isMinorBreak(c))
			type = Type.MINOR_BREAK;
		else
			type = Type.GLUE;
		
		int start = inx;
		int end = s.length();
		
		char prev = c;
		for(int i=inx+1;i<s.length();i++,prev=c){
			c = s.charAt(i);
			if(type == Type.TEXT && !isText(c,prev)){
				end = i;
				break;
			} else if(type != Type.TEXT){
				// minor break markers are not saved, we infere them
				if(FastWikiTokenizerEngine.isMinorBreak(c))
					type = Type.MINOR_BREAK;
				
				if(isText(c,prev)){
					end = i;
					break;
				}					
			}
		}
		return new ExtToken(s.substring(start,end),start,end,type,null);
	}
	
	private static void tokenize(ArrayList<ExtToken> tokens, String s, Position pos){
		for(int i=0;i<s.length();){
			ExtToken t = getToken(s,i);
			t.setPosition(pos);
			tokens.add(t);
			i += t.termText().length();						
		}
	}
	
	public static ArrayList<ExtToken> deserialize(byte[] serialized){
		if(decomposer == null)
			decomposer = UnicodeDecomposer.getInstance();
		ArrayList<ExtToken> tokens = new ArrayList<ExtToken>();
		
		HashMap<Integer,Position> posMap = new HashMap<Integer,Position>();
		for(Position p : Position.values())
			posMap.put(p.ordinal(),p);
		
		Position pos = posMap.get((int)serialized[0]);
		
		int cur = 1; // current position in serialized
		try {
			//System.out.println("SERIALIZED: "+new String(serialized,0,serialized.length,"utf-8"));
			for(;cur < serialized.length;){
				int controlInx = findControl(serialized,cur);
				if(controlInx != cur){
					// get valid utf-8 string until the first control sequence
					String s = new String(serialized,cur,controlInx-cur,"utf-8");
					tokenize(tokens,s,pos);
				}
				ExtToken t = (tokens.size() == 0)? null : tokens.get(tokens.size()-1);
				
				cur = controlInx+1;
				if(cur >= serialized.length)
					break;
				int control = serialized[cur++];
				switch(control){
				case 1: // original
					{ int len = serialized[cur++]&0xff;
					t.setOriginal(new String(serialized,cur,len,"utf-8"));
					if(t.type != Type.TEXT || t.getPositionIncrement()==0)
						raiseException(serialized,cur,t,"Bad serialized data: trying to assign original string to nontext token or alias");
					cur += len;
					break; }
				case 2: // alias
					{ int len = serialized[cur++]&0xff;
					ExtToken tt = new ExtToken(new String(serialized,cur,len,"utf-8"),t.startOffset(),t.endOffset(),t.type,t.pos);
					tt.setPositionIncrement(0);
					tokens.add(tt);
					if(t.type != Type.TEXT)						
						raiseException(serialized,cur,t,"Bad serialized data: trying to assign alias to nontext token");						
					cur += len;
					break; }
				case 3: // change pos
					pos = posMap.get((int)(serialized[cur++]&0xff));
					t.setPosition(pos);
					break;
				case 4: // sentence break
					if(t.type == Type.TEXT)
						raiseException(serialized,cur,t,"Bad serialized data: trying to assing a sentence break to text");
					t.setType(Type.SENTENCE_BREAK);
					break;
				case 5: // url
					{ int len = serialized[cur++]&0xff;
					/* int controlEnd = findControl(serialized,cur);
					int len = controlEnd - cur; */
					ExtToken tt = new ExtToken(new String(serialized,cur,len,"utf-8"),cur,cur+len,Type.URL,Position.EXT_LINK);
					tokens.add(tt);
					cur += len;
					break; }
				case 6: // original is title case
					t.setOriginal(t.termText().substring(0,1).toUpperCase()+t.termText().substring(1));
					break;
				case 7: // original is upper case
					t.setOriginal(t.termText().toUpperCase());
					break;					
				default:
					throw new RuntimeException("Unkown control sequence "+control);
				}				
				
			}
		} catch (UnsupportedEncodingException e) {
			e.printStackTrace();
		}

		
		return tokens;
	}

	private static void raiseException(byte[] serialized, int cur, ExtToken t, String string) {
		try {
			int len = Math.min(40,serialized.length-cur+10);
			log.error(string+", token="+t+", around: "+new String(serialized,cur-10,len,"utf-8"));
		} catch (UnsupportedEncodingException e) {
			e.printStackTrace();
		};
		throw new RuntimeException(string);
		
	}
	

}
