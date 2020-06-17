<?php

declare(strict_types=1);

namespace bvtvd\HyperfLogViewer;

use Hyperf\Contract\ConfigInterface;

class LogViewer 
{   
    /**
     * @var string
     */
    public $file;

    /**
     * 日志文件所在目录
     * @var string
     */
    public $dir;

    public $config;

    private $levels_classes = [
        'debug' => 'info',
        'info' => 'info',
        'notice' => 'info',
        'warning' => 'warning',
        'error' => 'danger',
        'critical' => 'danger',
        'alert' => 'danger',
        'emergency' => 'danger',
        'processed' => 'info',
        'failed' => 'warning',
    ];

    private $levels_imgs = [
        'debug' => 'info-circle',
        'info' => 'info-circle',
        'notice' => 'info-circle',
        'warning' => 'exclamation-triangle',
        'error' => 'exclamation-triangle',
        'critical' => 'exclamation-triangle',
        'alert' => 'exclamation-triangle',
        'emergency' => 'exclamation-triangle',
        'processed' => 'info-circle',
        'failed' => 'exclamation-triangle'
    ];

    /**
     * Log levels that are used
     * @var array
     */
    private $log_levels = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
        'processed',
        'failed'
    ];

    /**
     * 最大展示文件 50M
     */
    const MAX_FILE_SIZE = 52428800;
    
    public function __construct(ConfigInterface $config)
    {
        $this->dir = $config->get('log_viewer.path');
        $this->config = $config;
    }

    /**
     * 获取日志文件路径
     */
    public function pathToLogFile($file)
    {
        $file = sprintf('%s/%s', $this->dir, $file);

        return $file;
    }

    public function setFile($file)
    {
        $this->file = $this->pathToLogFile($file);
    }

    public function getFileName()
    {
        return basename($this->file);
    }

    /**
     * 获取所有文件
     * @param bool $basename
     * @return array
     */
    public function getFiles($basename = false)
    {
        $files = glob($this->dir. '/' . $this->config->get('log_viewer.pattern'));

        $files = array_reverse($files);
        $files = array_filter($files, 'is_file');
        if ($basename && is_array($files)) {
            foreach ($files as $k => $file) {
                $files[$k] = basename($file);
            }
        }
        return array_values($files);

    }
    

    public function logs()
    {
        $log = array();

        $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?\].*/';

        if(!$this->file){
            $logFiles = $this->getFiles();

            if(!count($logFiles)){
                return [];
            }
            $this->file = $logFiles[0];
        }

        if(!file_exists($this->file)) return [];

        if(filesize($this->file) > self::MAX_FILE_SIZE) return null;

        $file = file_get_contents($this->file);

        preg_match_all($pattern, $file, $headings);

        if (!is_array($headings)) return $log;

        $log_data = preg_split($pattern, $file);

        if ($log_data[0] < 1) {
            array_shift($log_data);
        }

        foreach ($headings as $h) {
            for ($i=0, $j = count($h); $i < $j; $i++) {
                foreach ($this->log_levels as $level) {
                    if (strpos(strtolower($h[$i]), '.' . $level) || strpos(strtolower($h[$i]), $level . ':')) {

                        preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?)\](?:.*?(\w+)\.|.*?)' . $level . ': (.*?)( in .*?:[0-9]+)?$/i', $h[$i], $current);
                        if (!isset($current[4])) continue;

                        $log[] = array(
                            'context' => $current[3],
                            'level' => $level,
                            'level_class' => $this->levels_classes[$level],
                            'level_img' => $this->levels_imgs[$level],
                            'date' => $current[1],
                            'text' => $current[4],
                            'in_file' => isset($current[5]) ? $current[5] : null,
                            'stack' => preg_replace("/^\n*/", '', $log_data[$i])
                        );
                    }
                }
            }
        }

        return array_reverse($log);
    }
}