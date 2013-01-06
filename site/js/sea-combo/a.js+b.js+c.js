define('./a',function(require){
	var d = require('d');console.log('a require d',d);
	return {
		'name': 'have id a.js',
		'ext': d
	}
});
define(function(require){
	return {
		'name': 'b.js'
	}
});
define(function(require){
	return {
		'name': 'c.js'
	}
});