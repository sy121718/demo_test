<?php
namespace app;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\exception\RouteNotFoundException;
use think\exception\ClassNotFoundException;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // ===== 业务层主动抛出的异常 =====
        // 通过 api_error() 函数抛出的异常，状态码由业务层控制
        // 例如：api_error('数据不存在', 404)
        if ($e instanceof \BusinessException) {
            $response = [
                'msg' => $e->getMessage(),
                'data' => null,
                'code' => $e->getCode()
            ];
            
            // 开发环境添加调试信息
            if (app()->isDebug() && $e->debugInfo !== null) {
                $response['debug'] = $e->debugInfo;
            }
            
            return json($response, $e->httpCode);
        }
        
        // ===== ThinkPHP框架自动抛出的异常 =====
        // 只有不是业务异常时，才检查框架异常
        // 处理数据库查询异常（框架自动抛出）
        // 触发场景：User::findOrFail($id)、User::where()->findOrFail() 等
        // 是否需要：可选，如果不用 findOrFail 方法就不需要
        // HTTP状态码：500，响应体code：500
        if ($e instanceof DataNotFoundException || $e instanceof ModelNotFoundException) {
            $response = [
                'msg' => '数据不存在',
                'data' => null,
                'code' => 500
            ];
            
            // 开发环境添加调试信息
            if (app()->isDebug()) {
                $response['debug'] = [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
            }
            
            return json($response, 200);
        }
        
        // 处理验证器异常（框架自动抛出）
        // 触发场景：$validate->failException()->check($data) 验证失败时
        // 是否需要：可选，如果手动检查验证结果就不需要
        // HTTP状态码：200，响应体code：200
        if ($e instanceof ValidateException) {
            $response = [
                'msg' => $e->getError(),
                'data' => null,
                'code' => 200
            ];
            
            // 开发环境添加调试信息
            if (app()->isDebug()) {
                $response['debug'] = [
                    'exception' => get_class($e),
                    'validation_rule' => $e->getError()
                ];
            }
            
            return json($response, 200);
        }
        
        // 处理路由异常（框架自动抛出）
        // 触发场景：访问不存在的路由
        // HTTP状态码：404，响应体code：404
        if ($e instanceof RouteNotFoundException) {
            $response = [
                'msg' => '访问资源不存在',
                'data' => null,
                'code' => 404
            ];
            
            // 开发环境添加调试信息
            if (app()->isDebug()) {
                $response['debug'] = [
                    'exception' => get_class($e),
                    'route' => $e->getMessage()
                ];
            }
            
            return json($response, 200);
        }
        
        // 处理类不存在异常（框架自动抛出）
        // 触发场景：调用不存在的控制器或服务类
        // HTTP状态码：500，响应体code：500
        if ($e instanceof ClassNotFoundException) {
            $response = [
                'msg' => app()->isDebug() ? '类不存在：' . $e->getMessage() : '服务暂时不可用',
                'data' => null,
                'code' => 500
            ];
            
            // 开发环境添加调试信息
            if (app()->isDebug()) {
                $response['debug'] = [
                    'exception' => get_class($e),
                    'class' => $e->getMessage()
                ];
            }
            
            return json($response, 200);
        }

        // ===== 其他系统异常 =====
        // 真正的系统错误，如语法错误、致命错误、类不存在等
        // HTTP状态码：500，响应体code：500
        $response = [
            'msg' => app()->isDebug() ? $e->getMessage() : '服务器内部错误',
            'data' => null,
            'code' => 500
        ];
        
        // 开发环境添加详细调试信息
        if (app()->isDebug()) {
            $response['debug'] = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }
        
        return json($response, 200);
    }
}
