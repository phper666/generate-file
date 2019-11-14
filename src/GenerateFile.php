<?php
/**
 * 主要功能是：
 * 1、替换某个项目下的所有文件内容
 * 2、能复制原目录文件生成一个内容被替换过新的项目
 * Created by PhpStorm.
 * User: liyuzhao
 * Date: 2019-11-14
 * Time: 10:33
 * @author liyuzhao
 * @emil 562405704@qq.com
 */
namespace Phper666\GenerateFile;

class GenerateFile
{
    /**
     * 文件名和文件内容需要替换的数据
     *  ['{$addons_name}' => 'wxapp-template',
     *  '{$addons_namespace}' => 'WxappTemplate',
     *  '{$addons_file_name}' => 'wxapp_template',
     *  '{$addons_config_application}' => []]
     * @var array
     */
    public $replaceParams = [];

    // 替换目录文件路径，必须为绝对路径
    public $replaceDir = '';

    // 替换的文件后缀，默认为所有文件都替换,
    public $replaceExt = ['*'];

    // 替换时是否生成新的目录，如果不为空会默认生成一个项目跟原文件目录一样，并且会自动替换新生成项目的文件和内容，为空则会直接替换当前项目文件和内容
    public $projectName = '';

    // 是否开启文件名替换，默认开启
    public $isReplaceFileName = true;

    /**
     * 运行
     * @return bool
     */
    public function run()
    {
        $originalFiles = $this->scanDir($this->replaceDir);

        // 只修改指定文件后缀的文件
        if (!in_array('*', $this->replaceExt)) {
            foreach ($originalFiles as $k => $v) {
                $arr = explode('.', $v);
                if (!empty($arr[1]) && !in_array($arr[1], $this->replaceExt)) {
                    unset($originalFiles[$k]);
                }
            }
        }
        $newFiles = $originalFiles;
        if ($this->isReplaceFileName) { // 开启文件名称替换
            $newFiles = $this->replaceFilesName($originalFiles);
        }

        if ($this->projectName != '') { // 生成新目录
            $newFiles = $this->makeNewProject($newFiles);
        }

        return $this->handleFiles($originalFiles, $newFiles);
    }

    /**
     * 替换文件内容，并且生成新文件
     * @param array $originalFiles
     * @param array $newFiles
     * @return bool
     */
    protected function handleFiles(array $originalFiles = [], array $newFiles = [])
    {
        foreach ($originalFiles as $index => $filePath) {
            $file = file_get_contents($filePath);
            $search = array_keys($this->replaceParams);
            $replaceValue = array_values($this->replaceParams);
            $file = str_replace($search, $replaceValue, $file);
            if (!empty($newFiles[$index])) {
                file_put_contents($newFiles[$index], $file);
                // 如果开启文件名称替换、并且文件名称改过,则删除原文件
                if ($this->isReplaceFileName && $filePath != $newFiles[$index]) {
                    @unlink($filePath);
                }
            }
        }
        return true;
    }

    /**
     * 替换文件的名称
     * @param $files
     * @return array
     */
    protected function replaceFilesName($files)
    {
        $newFiles = [];
        $search = array_keys($this->replaceParams);
        $replaceValue = array_values($this->replaceParams);
        foreach ($files as $k => $v) {
            $arr = explode('.', $v);
            if (!empty($arr[1])) {
                $tmp = explode('/', $arr[0]);
                $end = array_pop($tmp);
                if (!empty($end)) {
                    $newFiles[$k] = implode('/', $tmp) . '/' . str_replace($search, $replaceValue, $end) . '.' . $arr[1];
                } else {
                    $newFiles[$k] = implode('/', $tmp) . '/' . '.' . $arr[1];
                }
            }
        }
        return $newFiles;
    }

    /**
     * 创建新目录和文件
     * @param $newFiles
     * @return array
     */
    protected function makeNewProject($newFiles)
    {
        // 替换项目目录
        $dir = explode('/', $this->replaceDir);
        $dirName = array_pop($dir); // 去掉最后一个数据
        $dir[] = $this->projectName;
        $dir = implode('/', $dir);
        $newFiles = str_replace($this->replaceDir, $dir, $newFiles);
        if ($dirName == $this->projectName) { // 如果是原项目，直接返回，没有必要跑生成目录
            return $newFiles;
        }
        $this->makeDirectory($newFiles); // 生成新的目录
        return $newFiles;
    }

    /**
     * 设置替换文件名称和文件内容的参数
     * @param array $params
     * @return $this
     */
    public function setReplaceParams(array $params)
    {
        $this->replaceParams = $params;
        return $this;
    }

    /**
     * 设置要替换的目录路径
     * @param string $dir
     * @return $this
     */
    public function setReplaceDir($dir)
    {
        $this->replaceDir = $dir;
        return $this;
    }

    /**
     * 设置替换文件名称和文件内容的参数
     * @param array $params
     * @return $this
     */
    public function setReplaceFileExt(array $params = ['*'])
    {
        $this->replaceExt = $params;
        return $this;
    }

    /**
     * 设置是否要生成新的目录文件
     * @param string $fileName
     * @return $this
     */
    public function setProjectName($fileName)
    {
        $this->projectName = $fileName;
        return $this;
    }

    /**
     * 设置是否更改符合替换变量的文件名称
     * @param bool $fl
     * @return $this
     */
    public function setReplaceFileName($fl)
    {
        $this->isReplaceFileName = $fl;
        return $this;
    }

    /**
     * 设置默认值，解决常驻内存单例的问题
     * @return $this
     */
    public function setDefaultParams()
    {
        $this->replaceParams = [];
        $this->replaceDir = '';
        $this->replaceExt = ['*'];
        $this->isReplaceFileName = true;
        $this->projectName = '';
        return $this;
    }

    /**
     * 循环路径生成不存在的目录
     * @param $files
     * @return bool
     */
    protected function makeDirectory($files)
    {
        foreach ($files as $path) {
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }
        }
        return true;
    }

    /**
     * 递归获取文件夹内所有文件夹和文件名称
     * @param        $dir
     * @param array  $ext 只获取指定后缀文件数据
     * @return array
     */
    protected function scanDir($dir, $ext = ['*'])
    {
        $files = [];
        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                while (($file = readdir($handle)) !== false) {
                    if ($file != "." && $file != "..") {
                        //判断子目录是否还存在子目录
                        if (is_dir($dir . "/" . $file)) {
                            //递归调用本函数，再次获取目录
                            $files = array_merge($files, $this->scanDir($dir . "/" . $file, $ext));
                        } else {
                            //获取目录数组
                            $file = pathinfo($file);
                            if (in_array('*', $ext)) {
                                $files[] = $dir . "/" . $file['basename'];
                            } else {
                                if (in_array($file['extension'], $ext)) {
                                    $files[] = $dir . "/" . $file['basename'];
                                }
                            }
                        }
                    }
                }
                closedir($handle);
                return $files;
            }
        }
    }
}


