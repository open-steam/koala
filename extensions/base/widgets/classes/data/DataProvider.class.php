<?php
namespace Widgets;

class DataProvider {
	
	static function attributeProvider($attribute, $initValue = null) {
		return new AttributeDataProvider($attribute, $initValue);
	}
	
	static function contentProvider($initValue = null) {
		return new ContentDataProvider($initValue);
	}
	
	static function staticProvider($data) {
		return new StaticDataProvider($data);
	}
	
	static function annotationDataProvider() {
		return new AnnotationDataProvider();
	}
        
        static function nameHTMLEncodeDataProvider() {
                return new NameHTMLEncodeDataProvider();
        }
	
}
?>