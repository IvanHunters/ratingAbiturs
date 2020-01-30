<?php namespace Volsu;
    class Cache
    {
        private $path;
        private $pathLoad;
        
        public function __construct($path){
            $this->pathLoad = $path.'/';
            $this->path = $_SERVER["DOCUMENT_ROOT"].$path.'/';

            if(!is_dir($this->path)){
                try{
                    mkdir($this->path, 0775, TRUE);//create folder recursive
                }catch(Exception $e){
                    var_dump($e->getMessage());
                }
            }
        }

        public function existFile($filename){
            if(!file_exists($this->path.$filename)){
                return FALSE;
            }
            return TRUE;
        }

        public function write($filename, $content){
            if(file_put_contents($this->path.$filename, $content)){
                return $this->path.$filename;
            }
            return false;
        }

        public function read($filename){
            if($this->existFile($filename)){
                return file_get_contents($this->getPath($filename));
            }
        }

        public function getPath($filename){
            if($this->existFile($filename)){
                return $this->path.$filename;
            }
        }
        
        public function getPathDownload($filename)
        {
            if($this->existFile($filename)){
                return $this->pathLoad.$filename;
            }
        }

        public function __toString(){
            return $this->path;
        }
    }