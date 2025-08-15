<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;
use think\Response;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {}

 

    /**
     * 验证方法（返回JSON格式）
     * @access protected
     * @param  array  $data      要验证的数据
     * @param  string $validator 验证器类名
     * @param  string $scene     验证场景（可选）
     * @param  bool   $batch     是否批量验证（可选）
     * @return \think\Response|true  验证成功返回true，失败返回JSON响应
     */
    protected function validate(array $data, string $validator, string $scene = '', bool $batch = false)
    {
        // 解析验证器类名
        $class = str_contains($validator, '\\') ? $validator : $this->app->parseClass('validate', $validator);
        
        // 检查验证器类是否存在
        if (!class_exists($class)) {
            $result = [
                'code' => 500,
                'message' => "验证器类 {$class} 不存在",
            ];
            
            return json($result);
        }
        
        $v = new $class();
        if (!empty($scene)) {
            $v->scene($scene);
        }
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }
        if (!$v->check($data)) {
            $result = [
                'code' => 400,
                'message' => $v->getError(),
                'timestamp' => time()
            ];
            
            return json($result);
        }

        // 验证成功
        return true;
    }



  
   

 



    /**
     * 成功返回
     * @param mixed $data 返回数据
     * @param string $message 返回消息
     * @return Response
     */
    protected function success($data): Response
    {
        return json($data,200);
    }

    /**
     * 失败返回
     * @param string $message 错误消息
     * @param mixed $data 返回数据
     * @return Response
     */
    protected function error($data): Response
    {
        return json($data,500);
    }

    /**
     * 权限不足返回
     * @param string $message 错误消息
     * @return Response
     */
    protected function forbidden(string $message = '权限不足'): Response
    {
        $result = [
            'code' => 403,
            'message' => $message,
            'data' => [],
            'timestamp' => time()
        ];
        
        return json($result);
    }

    /**
     * 资源不存在返回
     * @param string $message 错误消息
     * @return Response
     */
    protected function notFound(string $message = '资源不存在'): Response
    {
        $result = [
            'code' => 404,
            'message' => $message,
            'data' => [],
            'timestamp' => time()
        ];
        
        return json($result);
    }
}
