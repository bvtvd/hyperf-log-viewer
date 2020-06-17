<?php

declare(strict_types=1);

namespace bvtvd\HyperfLogViewer\Controller;

use bvtvd\HyperfLogViewer\LogViewer;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\View\RenderInterface;

class LogController
{


    public function index(LogViewer $logViewer, RenderInterface $render, RequestInterface $request, ResponseInterface $response)
    {
        /**
         * 下载
         */
        if($dl = $request->input('dl')){
            return $response->download($logViewer->pathToLogFile($dl));
        }

        /**
         * 删除
         */
        if($del = $request->input('del')){
            unlink($logViewer->pathToLogFile($del));
            return $response->redirect($request->url());
        };

        $files = $logViewer->getFiles(true);

        if($request->has('delall')){
            foreach($files as $file){
                unlink($logViewer->pathToLogFile($file));
            }
            return $response->redirect($request->url());
        }

        if($currentFile = $request->input('l')){
            $logViewer->setFile($currentFile);
        }

        return $render->render('log', [
            'logs' => $logViewer->logs(),
            'files' => $logViewer->getFiles(true),
            'current_file' => $logViewer->getFileName()
        ]);
    }
}