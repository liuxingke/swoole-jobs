<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) kcloze <pei.greet@qq.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kcloze\Jobs\Queue;

use Kcloze\Jobs\JobObject;

class RedisTopicQueue extends BaseTopicQueue
{
    /**
     * RedisTopicQueue constructor.
     * 使用依赖注入的方式.
     *
     * @param \Redis $redis
     */
    public function __construct(\Redis $redis)
    {
        $this->queue = $redis;
    }

    /*
     * push message to queue.
     *
     * @param [string] $topic
     * @param [sting]  $value
     * @param [int]    $delay    延迟毫秒
     * @param [int]    $priority 优先级
     * @param [int]    $expiration      过期毫秒
     */
    public function push($topic, JobObject $job, $delay=0, $priority=BaseTopicQueue::HIGH_LEVEL_1, $expiration=0)
    {
        if (!$this->isConnected()) {
            return;
        }

        return $this->queue->lPush($topic, serialize($job));
    }

    public function pop($topic)
    {
        if (!$this->isConnected()) {
            return;
        }

        $result = $this->queue->lPop($topic);

        return !empty($result) ? @unserialize($result) : null;
    }

    public function len($topic)
    {
        if (!$this->isConnected()) {
            return 0;
        }

        return (int) $this->queue->lSize($topic) ?? 0;
    }

    public function close()
    {
        if (!$this->isConnected()) {
            return;
        }

        $this->queue->close();
    }

    public function isConnected()
    {
        try {
            $this->queue->ping();
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;

            return false;
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;

            return false;
        }

        return true;
    }
}
