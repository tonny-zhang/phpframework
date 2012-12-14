<?php
/**
 * Helper_Filesys 类提供了一组简化文件系统操作的方法
 */
abstract class Helper_Filesys
{
    /**
     * 遍历指定目录及子目录下的文件，返回所有与匹配模式符合的文件名
     */
    static function recursionGlob($dir, $pattern)
    {
        $dir = rtrim($dir, '/\\') . DS;
        $files = array();

        // 遍历目录，删除所有文件和子目录
        $dh = opendir($dir);
        if (!$dh) return $files;

        $items = (array)glob($dir . $pattern);
        foreach ($items as $item)
        {
            if (is_file($item)) $files[] = $item;
        }

        while (($file = readdir($dh)))
        {
            if ($file == '.' || $file == '..') continue;

            $path = $dir . $file;
            if (is_dir($path))
            {
                $files = array_merge($files, self::recursionGlob($path, $pattern));
            }
        }
        closedir($dh);

        return $files;
    }

    /**
     * 创建一个目录树，失败抛出异常
     *
     */
    static function mkdirs($dir, $mode = 0777)
    {
        if (!is_dir($dir))
        {
            $ret = @mkdir($dir, $mode, true);
            if (!$ret)
            {
                throw new FDX_Exception("创建目录失败".$dir);
            }
        }
        return true;
    }

    /**
     * 删除指定目录及其下的所有文件和子目录，失败抛出异常
     *
     */
    static function rmdirs($dir)
    {
        $dir = realpath($dir);
        if ($dir == '' || $dir == '/' || (strlen($dir) == 3 && substr($dir, 1) == ':\\'))
        {
            // 禁止删除根目录
            throw new FDX_Exception("禁止删除根目录:".$dir);
        }

        // 遍历目录，删除所有文件和子目录
        if(false !== ($dh = opendir($dir)))
        {
            while(false !== ($file = readdir($dh)))
            {
                if($file == '.' || $file == '..')
                {
                    continue;
                }

                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path))
                {
                    self::rmdirs($path);
                }
                else
                {
                    unlink($path);
                }
            }
            closedir($dh);
            if (@rmdir($dir) == false)
            {
                throw new FDX_Exception($dir);
            }
        }
        else
        {
            throw new FDX_Exception($dir);
        }
    }

    /**
     * 复制一个目录及其子目录的文件到目的地
     *
     */
    static function copyDir($src, $dst, $options=array())
    {
        $extnames = !empty($options['extnames'])
                ? Core::normalize($options['extnames'])
                : array();
        foreach ($extnames as $offset => $extname)
        {
            if ($extname[0] == '.')
            {
                $extnames[$offset] = substr($extname, 1);
            }
        }
        $excludes = !empty($options['excludes'])
                ? Core::normalize($options['excludes'])
                : array();
        $level    = isset($options['level'])
                ? intval($options['level'])
                : -1;
        self::_copyDirectoryRecursive($src, $dst, '', $extnames, $excludes, $level);
    }

    /**
     * 在指定目录及其子目录中查找文件
     */
    static function findFiles($dir, $options=array())
    {
        $extnames = !empty($options['extnames'])
                ? Core::normalize($options['extnames'])
                : array();
        foreach ($extnames as $offset => $extname)
        {
            if ($extname[0] == '.')
            {
                $extnames[$offset] = substr($extname, 1);
            }
        }
        $excludes = !empty($options['excludes'])
                ? Core::normalize($options['excludes'])
                : array();
        $level    = isset($options['level'])
                ? intval($options['level'])
                : -1;

        $list = self::_findFilesRecursive($dir, '', $extnames, $excludes, $level);
        sort($list);
        return $list;
    }

    /**
     * 内部使用
     */
    private static function _copyDirectoryRecursive($src, $dst, $base, $extnames, $excludes, $level)
    {
        @mkdir($dst);
        @chmod($dst,0777);
        $folder = opendir($src);
        while (($file = readdir($folder)))
        {
            if ($file{0} == '.') continue;
            $path = $src . DIRECTORY_SEPARATOR . $file;
            $is_file = is_file($path);
            if(self::_validatePath($base, $file, $is_file, $extnames, $excludes))
            {
                if($is_file)
                {
                    copy($path, $dst . DIRECTORY_SEPARATOR . $file);
                }
                elseif($level)
                {
                    self::_copyDirectoryRecursive($path, $dst . DIRECTORY_SEPARATOR . $file,
                            $base . '/' . $file, $extnames, $excludes, $level - 1);
                }
            }
        }
        closedir($folder);
    }


    /**
     * 递归查找文件，用于 {@link Helper_FileSys::findFiles()}
     */
    private static function _findFilesRecursive($dir, $base, $extnames,
            $excludes, $level)
    {
        $list = array();
        $handle = opendir($dir);
        while(($file = readdir($handle)))
        {
            if($file == '.' || $file == '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $file;

            $is_file = is_file($path);
            if (self::_validatePath($base, $file, $is_file, $extnames, $excludes))
            {
                if ($is_file)
                {
                    $list[] = $path;
                }
                elseif ($level)
                {
                    $list = array_merge($list, self::_findFilesRecursive($path,
                            $base . '/' . $file, $extnames, $excludes, $level - 1));
                }
            }
        }
        closedir($handle);
        return $list;
    }

    /**
     * 验证文件或目录，返回验证结果
     *
     */
    private static function _validatePath($base, $file, $is_file,
            array $extnames, array $excludes)
    {
        $test = ltrim(str_replace('\\', '/', "/{$base}/{$file}"), '/');
        foreach($excludes as $e)
        {
            if ($file == $e || $test == $e) return false;
        }
        if(!$is_file || empty($extnames)) return true;
        
        if(($pos = strrpos($file, '.')) !==false)
        {
            $type = substr($file, $pos + 1);
            return in_array($type, $extnames);
        }
        else
        {
            return false;
        }
    }
}

