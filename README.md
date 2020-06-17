
发布配置文件
```
php bin/hyperf.php vendor:publish bvtvd/hyperf-log-viewer
```
该命令会发布一个配置文件log_viewer.php 到 config/autoload 下, 发布一个视图文件 log.blade.php 到 storage/view 下


在 config/routes 文件中自定义查看日志的访问路由, 如:
```
Router::get('/logs', 'bvtvd\HyperfLogViewer\Controller\LogController@index');
```

### 注意
- 该包需要安装 hyperf/view, 还需要 duncan3dc/blade 模板引擎提供支持, 慎用. 
- 视图配置如下
```
use Hyperf\View\Engine\BladeEngine;
use Hyperf\View\Mode;

return [
    'engine' => BladeEngine::class,
    'mode' => Mode::TASK,
    'config' => [
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
    ],
];
```

- 需要将日志文件按日志进行分割
- 可以选择将程序运行异常输出到日志文件, 修改 app\Exception\Handler\AppExceptionHandler.php 文件如下: 
```
class AppExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        ApplicationContext::getContainer()->get(LoggerFactory::class)->make()->error(sprintf("%s[%s] in %s\n[stacktrace]\n%s", $throwable->getMessage(), $throwable->getLine(), $throwable->getFile(), $throwable->getTraceAsString()));

        return $response->withHeader("Server", "Hyperf")->withStatus(500)->withBody(new SwooleStream('Internal Server Error.'));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
```