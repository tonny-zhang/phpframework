(function(){
  var pluginSDK = seajs.pluginSDK
  var util = pluginSDK.util
  var config = pluginSDK.config

  var Module = pluginSDK.Module
  var cachedModules = seajs.cache

  var STATUS = Module.STATUS;
  var MP = Module.prototype

  var comboingModules = [];//要合并的模块
  function getComboSyntax(){
  	return config.comboSyntax || '+';//合并标识
  }
  function isComboUri(uri){
  	var comboSyntax = getComboSyntax();
    return ~uri.indexOf(comboSyntax) || ~util.parseMap(uri).indexOf(comboSyntax);
  }
  /**
   * combo文件并添加map
   */
  var _load = MP._load;
  MP._load = function(uris, callback){
    var base = config.base || './';
    if(!util.isAbsolute(base)){
      base = util.realpath(util.pageUri+base);
    }
    
    uris = util.unique(uris);
    //引用单个模块，不用combo
    //[a,a] -> [a]
    if(uris.length > 1){
      var unFetchedUris = [];
      var comboExcludes = config.comboExcludes;
      util.forEach(uris,function(uri){
        var module = cachedModules[uri]

        if (!module || module.status < STATUS.FETCHING) {

          // Parse map before pushing
          var requestUri = util.parseMap(uri);
          unFetchedUris.push(uri.substr(uri.indexOf(base)+base.length));
        }
      });
      //把没有加载的模块到combo的映射添加到config中
      if(unFetchedUris.length > 0){
        var comboUri = Module._resolve(unFetchedUris.join(getComboSyntax()));
        var map = [];
        util.forEach(unFetchedUris,function(uri){
          map.push([Module._resolve(uri),comboUri]);
        });
        seajs.config({'map': map});
      }
    }
    
    _load.call(this,uris,callback);
  }
  var _define = Module._define;
  Module._define   = function(id, deps, factory){
    var argsLength = arguments.length
    if (argsLength === 1){
      factory = id
      id = undefined
    }else if (argsLength === 2){
      factory = deps
      deps = undefined

      if (util.isArray(id)){
        deps = id
        id = undefined
      }
    }

    // Parses dependencies.
    if (!util.isArray(deps) && util.isFunction(factory)) {
      deps = util.parseDependencies(factory.toString())
    }
    //NOTICE:这里不用_define里的util.getCurrentScript()
    //在下面的_fetch里会重写combo的meta.id
    var meta = { id: Module._resolve(id), dependencies: deps, factory: factory }
    comboingModules.push(meta);
  }
  window.define = Module._define;

  var _fetch = Module._fetch;
  Module._fetch = function(url, callback, charset){
    _fetch.call(this,url, function(){
      var fetchedModules = [];
      function setMetaId(id){
        var comboModule = comboingModules.shift();
        comboModule.id = id;
        fetchedModules.push(comboModule);
      }
      if(isComboUri(url)){
        //当combo模块fetched后，使其它依赖子模块状态一致
        util.forEach(config.map,function(_map){
          if(_map[1] == url){
            var module = cachedModules[_map[0]];
            if (module && module.status === STATUS.FETCHING) {
              module.status = STATUS.FETCHED
            }
            setMetaId(_map[0]);
          }
        });
      }else{
        setMetaId(url);
      }
      //调用原始_define中save
      util.forEach(fetchedModules,function(module){
         // Module._save(module.id, module);
        _define.call(null,module.id,module.dependencies,module.factory);
      });
      util.isFunction(callback) && callback();
    }, charset);
  }
})();