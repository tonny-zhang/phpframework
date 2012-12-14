<?php
return array(
    'Module_Path' => PROJECT_BASE_PATH.'/site/modules/',

    'Block_Path'  => PROJECT_BASE_PATH.'/App/Block/Site/',

    'Lib_Path'    => PROJECT_BASE_PATH.'/Core/Lib/',

    'View_Neat' => array(
        'type' => 'Neat',
		'plugins' => array(
			'TemplatePlugin' => PROJECT_BASE_PATH.'/Core/Lib/NeatTemplate/Plungin/TemplatePlugin.class.php',
		),
        'template_path' => PROJECT_BASE_PATH.'/site/tpl/',
        'template_cache_path' => PROJECT_BASE_PATH.'/site/cache/tpl/',
    ),

    'Upload_Config' => array(
		'tmp_dir' => 'd:/',
		'test' => array(
            'upload' => '/upload/?mod=test',
		),
    ),

    'Site_Domain' => 'www.testphpframework.com',

    'Front_Cache_Version' => '20101020',
    'Front_Cache_Version_Min' => '20101020',

    'Front_Resource_Site' => 'www.testphpframework.com',
);
