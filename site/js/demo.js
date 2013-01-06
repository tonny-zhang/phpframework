function Demo(val){
	return val+' hello!';
}
define(function(require){
	//这里的依赖会用combo插件合并-> 
	//http://www.testphpframework.com/min/?f=/js/a.js,/js/b.js,/js/c.js&0.3191041755490005
	var a = require('js/a.js');
	// var b = require('js/b.js');
	// var c = require('js/c.js');
	console.log('loaded:',a);
	return {
		name: 'demo.js'
	}
})