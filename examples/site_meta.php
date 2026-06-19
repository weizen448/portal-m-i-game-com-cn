<?php

/**
 * SiteMeta - 站点元信息管理类
 * 用于存储、处理站点描述性数据，并提供统一的字符描述输出。
 */
class SiteMeta
{
    /**
     * @var array 站点原始配置数据
     */
    private array $data;

    /**
     * 构造函数：初始化站点信息数组
     *
     * @param array $config 可选的自定义配置，若不提供则使用默认示例数据
     */
    public function __construct(array $config = [])
    {
        // 默认站点元数据示例（包含关联 URL 与核心关键词）
        $default = [
            'site_name'        => '爱游戏门户',
            'domain'           => 'https://portal-m-i-game.com.cn',
            'keywords'         => ['爱游戏', '游戏资讯', '玩家社区'],
            'description'      => '爱游戏门户提供最新游戏动态、深度评测与玩家互动。',
            'language'         => 'zh-CN',
            'charset'          => 'UTF-8',
            'author'           => 'GamePortal Team',
            'last_updated'     => '2025-04-10',
            'enable_short_desc'=> true,
            'short_desc_limit' => 80,
        ];

        $this->data = array_merge($default, $config);
    }

    /**
     * 设置或覆盖某个元字段
     *
     * @param string $key   字段名称
     * @param mixed  $value 字段值
     * @return self 支持链式调用
     */
    public function set(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * 获取原始元数据
     *
     * @param string|null $key 若提供则返回该字段，否则返回全部
     * @return mixed
     */
    public function get(?string $key = null)
    {
        if ($key === null) {
            return $this->data;
        }
        return $this->data[$key] ?? null;
    }

    /**
     * 生成简短描述文本（自动截断并添加省略号）
     *
     * @param int|null $maxLength 最大长度，不传则使用配置中的 short_desc_limit
     * @return string 处理后的描述字符串（HTML 安全转义）
     */
    public function generateShortDescription(?int $maxLength = null): string
    {
        // 若关闭短描述功能，直接返回空字符串
        if (empty($this->data['enable_short_desc'])) {
            return '';
        }

        $limit = $maxLength ?? (int)($this->data['short_desc_limit'] ?? 80);
        $raw   = $this->data['description'] ?? '';

        // 如果描述为空，尝试用关键词拼接一段文字
        if (empty(trim($raw))) {
            $keywords = $this->data['keywords'] ?? ['爱游戏'];
            $raw = implode('、', $keywords) . ' — 欢迎访问 ' . ($this->data['site_name'] ?? '本站');
        }

        // 截断逻辑
        if (mb_strlen($raw) > $limit) {
            $short = mb_substr($raw, 0, $limit);
            // 避免在单词中间截断（中文无需处理，英文在空格处截断更友好）
            $lastSpace = mb_strrpos($short, ' ');
            if ($lastSpace !== false && $lastSpace > $limit * 0.6) {
                $short = mb_substr($short, 0, $lastSpace);
            }
            $short .= '...';
        } else {
            $short = $raw;
        }

        // HTML 输出转义，防止 XSS
        return htmlspecialchars($short, ENT_QUOTES | ENT_HTML5, $this->data['charset'] ?? 'UTF-8');
    }

    /**
     * 以关联数组形式返回用于模板的元数据
     *
     * @return array 包含 title、description、keywords（逗号分隔）等
     */
    public function toTemplateArray(): array
    {
        $keywordsArr = $this->data['keywords'] ?? ['爱游戏'];
        $keywordsStr = implode(', ', $keywordsArr);

        return [
            'title'       => htmlspecialchars($this->data['site_name'] ?? '', ENT_QUOTES),
            'description' => $this->generateShortDescription(),
            'keywords'    => htmlspecialchars($keywordsStr, ENT_QUOTES),
            'url'         => htmlspecialchars($this->data['domain'] ?? '', ENT_QUOTES),
            'author'      => htmlspecialchars($this->data['author'] ?? '', ENT_QUOTES),
        ];
    }

    /**
     * 直接输出完整的 <meta> 标签块（用于 <head> 内）
     *
     * @return void
     */
    public function renderMetaTags(): void
    {
        $meta = $this->toTemplateArray();
        echo '<meta charset="' . $this->data['charset'] . '">' . "\n";
        echo '<meta name="description" content="' . $meta['description'] . '">' . "\n";
        echo '<meta name="keywords" content="' . $meta['keywords'] . '">' . "\n";
        echo '<meta name="author" content="' . $meta['author'] . '">' . "\n";
        echo '<link rel="canonical" href="' . $meta['url'] . '">' . "\n";
    }
}

// ------------------- 使用示例（可移除，仅演示） -------------------
/*
$meta = new SiteMeta();
$meta->set('description', '爱游戏门户——带你发现最好玩的游戏世界。')
     ->set('short_desc_limit', 60);
echo $meta->generateShortDescription() . "\n";
$meta->renderMetaTags();
*/