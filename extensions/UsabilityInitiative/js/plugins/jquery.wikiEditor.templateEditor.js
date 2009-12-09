/* template forms module for wikiEditor */
( function( $ ) { $.wikiEditor.modules.templateEditor = {

/**
 * API accessible functions
 */
api: {
	//
},

evt: {
	mark: function(){	
			
			var findOutermostTemplates = function(){
				templateBeginFound = false;
				for(;i< tokenStack.length; i++){
					if(tokenStack[i].label == "TEMPLATE_BEGIN"){
						templateBeginFound = true;
					break;
					}
				}
				var j = i;
				i++;
				if(!templateBeginFound){
					return false;
				}
				else{
					// This is only designed to find the outermost template boundaries, the model handles nested template
					// and template-like objects better
					var nestedBegins = 1;
					while(nestedBegins > 0  && j < tokenStack.length){
						j++;
						if(tokenStack[j].label == "TEMPLATE_END"){
							nestedBegins--;
						}
						if(tokenStack[j].label == "TEMPLATE_BEGIN"){
							nestedBegins++;
						}
					}//while
					if(nestedBegins == 0){
						// outer template begins at tokenStack[i].offset
						// and ends at tokenStack[j].offset + 2
						var leftMarker = i -1;
						var rightMarker = j;
						i = j;
						return [ leftMarker, rightMarker ];
					}
					else{
						return false;
					}
				}
			}; //find outermost templates
			
			var markers = $.wikiEditor.modules.highlight.fn.markers;
			tokenStack = $.wikiEditor.modules.highlight.fn.tokenArray;
			var i=0;
			var templateBoundaries = findOutermostTemplates();
			
			templateBeginFound = false;
			
			while(templateBoundaries){
				if(typeof(markers[tokenStack[templateBoundaries[0]].offset]) == 'undefined'){
					markers[tokenStack[templateBoundaries[0]].offset] = new Array();
				}
				if(typeof(markers[tokenStack[templateBoundaries[1]].offset]) == 'undefined'){
					markers[tokenStack[templateBoundaries[1]].offset] = new Array();
				}
				
				markers[tokenStack[templateBoundaries[0]].offset].push("<div class='wiki-template'>");
				markers[tokenStack[templateBoundaries[1]].offset].push("</div>");
				
				templateBoundaries = findOutermostTemplates();
			}	
		}
},


exp: [
		{ regex: /{{/ , label: "TEMPLATE_BEGIN" },
		{ regex: /}}/ , label: "TEMPLATE_END"	, markAfter:true}
],

/**
 * Internally used functions
 */
fn: {
	/**
	 * Creates template form module within wikieditor
	 * @param context Context object of editor to create module in
	 * @param config Configuration object to create module from
	 */
	create: function( context, config ) {
		
		//check if text is selected
	
	
	}, 	

	//template Model
	model: function (wikitext){

		//param object
		function param(name, value, number, nameIndex, equalsIndex, valueIndex){
			this.name = name;
			this.value = value;
			this.number = number;
			this.nameIndex = nameIndex;
			this.equalsIndex = equalsIndex;
			this.valueIndex = valueIndex;
		}
		
		//range object
		function range(begin, end){
			this.begin = begin;
			this.end = end;
		}
		
		var ranges = new Array();
		var sanatizedStr = "";
		var params = new Array();
		var paramsByName = new Array();
		var templateNameIndex = 0;
		
		//markOffTemplates
		markOffTemplates = function () {
			sanatizedStr = wikitext;
			
			sanatizedStr = sanatizedStr.replace(/{{/, "  "); //get rid of first {{ with whitespace
			endBraces = sanatizedStr.match(/}}\s*$/); //replace end
			sanatizedStr = sanatizedStr.substring(0,endBraces.index) + "  " + sanatizedStr.substring(endBraces.index+2);
			
			while(sanatizedStr.indexOf('{{') != -1){
				startIndex = sanatizedStr.indexOf('{{') + 1;
				openBraces = 2;
				endIndex = startIndex;
				while(openBraces > 0){
					endIndex++;
					switch (sanatizedStr[endIndex]){
						case '}': openBraces--; break;
						case '{': openBraces++; break;
					}
				}
				sanatizedSegment = sanatizedStr.substring(startIndex,endIndex);
				sanatizedSegment = sanatizedSegment.replace(/}/g, "X");
				sanatizedSegment = sanatizedSegment.replace(/{/g, "X");
				sanatizedSegment = sanatizedSegment.replace(/\|/g, "X");
				sanatizedSegment = sanatizedSegment.replace(/=/g, "X");
				sanatizedStr = sanatizedStr.substring(0, startIndex) + sanatizedSegment + sanatizedStr.substring(endIndex);
			}		
		};

		// Whitespace* {{ whitespace* nonwhitespace:
		if(wikitext.match(/\s*{{\s*\S*:/)){
			//we have a parser function!
		}
		
		markOffTemplates();
		
		var doneParsing = false;
		oldDivider = 0;
		divider = sanatizedStr.indexOf('|', oldDivider);
		if(divider == -1){divider = sanatizedStr.length; doneParsing = true;}
		nameMatch = wikitext.substring(oldDivider, divider).match(/[^{\s]+/);
		if(nameMatch != undefined){
			ranges.push( new range(oldDivider,nameMatch.index) ); //whitespace and squiggles upto the name
			templateNameIndex = ranges.push( new range(nameMatch.index, nameMatch.index + nameMatch[0].length));
			templateNameIndex--; //push returns 1 less than the array
			ranges[templateNameIndex].old = wikitext.substring(ranges[templateNameIndex].begin, ranges[templateNameIndex].end);
		}
		params.push(ranges[templateNameIndex].old); //put something in params (0)

		currentParamNumber = 0;
		var valueEndIndex;
		while(!doneParsing){
			currentParamNumber++;
			oldDivider = divider;
			divider = sanatizedStr.indexOf('|', oldDivider+1);
			if(divider == -1){divider = sanatizedStr.length; doneParsing = true;}
			currentField = sanatizedStr.substring(oldDivider+1,divider);
			if(currentField.indexOf('=') == -1){
				//anonymous field, gets a number
				valueBegin = currentField.match(/\S+/); //first nonwhitespace character
				valueBeginIndex = valueBegin.index + oldDivider+1;
				valueEnd = currentField.match(/[^\s]\s*$/); //last nonwhitespace character
				valueEndIndex = valueEnd.index + oldDivider + 2;
				ranges.push(new range(ranges[ranges.length-1].end, valueBeginIndex) ); //all the chars upto now 
				nameIndex = ranges.push(new range(valueBeginIndex, valueBeginIndex));
				nameIndex--;
				equalsIndex = ranges.push(new range(valueBeginIndex, valueBeginIndex));
				equalsIndex--;
				valueIndex = ranges.push(new range(valueBeginIndex, valueEndIndex));
				valueIndex--;
				params.push(new param(currentParamNumber, 
									  wikitext.substring(ranges[valueIndex].begin, ranges[valueIndex].end), 
									  currentParamNumber, nameIndex, equalsIndex, valueIndex));
				paramsByName[currentParamNumber] = currentParamNumber;
			}
			else{
				//there's an equals, could be comment or a value pair
				currentName = currentField.substring(0, currentField.indexOf('='));
				//(still offset by oldDivider)
				nameBegin = currentName.match(/\S+/); //first nonwhitespace character
				if(nameBegin == null){ divider++; currentParamNumber--; continue;} // this is a comment inside a template call/parser abuse. let's not encourage it
				nameBeginIndex = nameBegin.index + oldDivider+1;
				nameEnd = currentName.match(/[^\s]\s*$/); //last nonwhitespace and non } character
				nameEndIndex = nameEnd.index + oldDivider+2;
			
				ranges.push(new range(ranges[ranges.length-1].end, nameBeginIndex) ); //all the chars upto now 
				nameIndex = ranges.push(new range(nameBeginIndex, nameEndIndex));
				nameIndex--;
				currentValue = currentField.substring(currentField.indexOf('=') + 1);
				oldDivider += (currentField.indexOf('=') +1);
				valueBegin = currentValue.match(/\S+/); //first nonwhitespace character
				valueBeginIndex = valueBegin.index + oldDivider + 1;
				valueEnd = currentValue.match(/[^\s]\s*$/); //last nonwhitespace and non } character
				valueEndIndex = valueEnd.index + oldDivider + 2;
				equalsIndex = ranges.push(new range(ranges[ranges.length-1].end, valueBeginIndex) ); //all the chars upto now 
				equalsIndex--;
				valueIndex = ranges.push(new range(valueBeginIndex, valueEndIndex));
				valueIndex--;
				params.push(new param(wikitext.substring(nameBeginIndex, nameEndIndex), 
						              wikitext.substring(valueBeginIndex, valueEndIndex), 
						              currentParamNumber, nameIndex, equalsIndex, valueIndex));
				paramsByName[wikitext.substring(nameBeginIndex, nameEndIndex)] = currentParamNumber;
			}
		}
		//the rest of the string
		ranges.push(new range(valueEndIndex, wikitext.length));
		
		//FUNCTIONS
		//set 'original' to true if you want the original value irrespective of whether the model's been changed
		getSetValue = function(name, value, original){
			var valueRange;
			var rangeIndex;
			var retVal;
			if(isNaN(name)){
				//it's a string!
				if(typeof(paramsByName[name]) == 'undefined'){
					//does not exist
					return "";
				}
				else{
					rangeIndex = paramsByName[name];
				}
			}
			else{
				//it's a number!
				rangeIndex = parseInt(name);
			}
			
			if(typeof(params[rangeIndex])  == 'undefined' ){
				//does not exist
				return "";
			}
			else{
				valueRange = ranges[params[rangeIndex].valueIndex];
			}
			
			if(typeof(valueRange.newVal) == 'undefined'  || original ){
				//value unchanged, return original wikitext
				retVal= wikitext.substring(valueRange.begin, valueRange.end);
			} 
			else{
				//new value exists, return new value
				retVal = valueRange.newVal;
			}
			
			if(value != null){
				ranges[params[rangeIndex].valueIndex].newVal = value;
			}
			
			return retVal;
		};
		
		//'public' functions
		
		//get template name
		this.getName = function(){
			if(typeof(ranges[templateNameIndex].newVal) == 'undefined'){
				return wikitext.substring(ranges[templateNameIndex].begin, ranges[templateNameIndex].end);
			}
			else{
				return ranges[templateNameIndex].newVal;
			}
		};
		
		//set template name (if we want to support this)
		this.setName = function(name){
			ranges[templateNameIndex].newVal = name;
		};
		
		//set value for a given param name/number
		this.setValue = function(name, value){
			return getSetValue(name, value, false);
		};

		//get value for a given param name/number
		this.getValue = function(name){
			return getSetValue(name, null, false);
		};
		
		//get original value of a param
		this.getOriginalValue = function(name){
			return getSetValue(name, null, true);
		};

		//get a list of all param names (numbers for the anonymous ones)
		this.getAllParams = function(){
			return paramsByName;
		};

		//get original template text
		this.getOriginalText = function(){
			return wikitext;
		};

		//get modified template text
		this.getText = function(){
			newText = "";
			for(i = 0 ; i < ranges.length; i++){
				if(typeof(ranges[i].newVal) == 'undefined'){
					newText += wikitext.substring(ranges[i].begin, ranges[i].end);
				}
				else{
					newText += ranges[i].newVal;
				}
			}
			return newText;
		};
		
	}//template model

		
	
	
	
}

}; } )( jQuery );


