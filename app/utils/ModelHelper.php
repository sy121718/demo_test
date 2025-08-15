<?php

namespace app\utils;

/**
 * 模型助手工具类
 * 专门用于模型相关的数据处理和查询条件构建
 */
class ModelHelper
{
    /**
     * 从原始数据中提取允许查询的字段并构建WHERE条件
     * @param array $originalData 原始请求数据
     * @param array $allowedFields 允许查询的字段列表
     * @return array 处理后的查询条件（ThinkPHP格式）
     */
    public static function buildWhereConditions(array $originalData, array $allowedFields): array
    {
        $result = [];
        
        foreach ($originalData as $field => $value) {
            // 跳过空值和不允许的字段
            if (!in_array($field, $allowedFields) || $value === '' || $value === null) {
                continue;
            }
            
            // 检查是否是操作符数组格式 ['<>', 0]
            if (is_array($value) && count($value) == 2 && is_string($value[0])) {
                // 转换为三元素格式：[field, operator, value]
                $result[] = [$field, $value[0], $value[1]];
            } else {
                // 等于条件也转换为三元素格式：[field, '=', value]
                $result[] = [$field, '=', $value];
            }
        }
        
        return $result;
    }
    

    
    /**
     * 提取排序条件（简化版 - 只支持单字段排序）
     * @param array $originalData 原始请求数据
     * @param array $allowedOrderFields 允许排序的字段列表
     * @return array 排序条件，格式：['field' => 'direction'] 或 空数组
     */
    public static function buildOrderCondition(array $originalData, array $allowedOrderFields): array
    {
        // 检查是否有order参数且在允许列表中
        if (isset($originalData['order']) && in_array($originalData['order'], $allowedOrderFields)) {
            $field = $originalData['order'];
            $direction = isset($originalData['order_direction']) && 
                        in_array(strtolower($originalData['order_direction']), ['asc', 'desc']) 
                        ? strtolower($originalData['order_direction']) : 'desc';
            
            return [$field => $direction];
        }
        
        return [];
    }
    
    /**
     * 数据安全过滤（只提取允许的字段，保持原格式）
     * @param array $originalData 原始数据
     * @param array $allowedFields 允许的字段列表
     * @return array 过滤后的数据
     */
    public static function filterSafeFields(array $originalData, array $allowedFields): array
    {
        $result = [];
        
        foreach ($allowedFields as $field) {
            if (isset($originalData[$field]) && $originalData[$field] !== '' && $originalData[$field] !== null) {
                $result[$field] = $originalData[$field];
            }
        }
        
        return $result;
    }
} 