# mobile-acg

![](https://badgen.net/badge/PHP/%3E=7.1/blue)
![](https://badgen.net/badge/php_extension/pdo_sqlite/blue)
![](https://badgen.net/badge/license/MIT/green)

一个PHP开发的HTTP API，随机获取一张或多张来自Telegram频道 [t.me/MikuArt](https://t.me/MikuArt) 的适合竖屏移动设备查看的二次元图片，支持直接反代。

网络上其实有很多类似的API，但基本都不开源，不能自行部署，服务稳定性没有保障。并且许多接口内置的图片较少，或者很久都不再更新了。所以我就自己写了一个。调用的是Telegram地址，并且可以自行部署，服务稳定性有保障。另外Telegram频道@MikuArt这个频道存在时间很长了，每天都维持着一个很高的更新频率。

## 使用API

我提供的API：<https://api.skyju.cc/mobile-acg/api.php>

所有图片均为jpg格式。

你也可以自己搭建（见下文）。

### 以JSON格式随机获取一张或多张图片的地址

| GET参数 | 值类型         | 是否可选 | 说明                                          |
| ------- | -------------- | -------- | --------------------------------------------- |
| method  | "json": String | 否       | 本接口规定的method值                          |
| count   | Int            | 是       | 1-1000的整数，指定返回图片的个数；不指定则为1 |

当请求成功时，返回JSON中的`data`是一个数组对象；否则为错误信息。

示例请求：

```bash
curl "https://api.skyju.cc/mobile-acg/api.php?method=json&count=2"
```

示例返回：

```json
{
    "status": true, # 若为false，则参数有误
    "data": [
        {
            "id": 5478, # 图片对应的ID
            "raw_url": "https://cdn4.telesco.pe/file/k5...WA.jpg", # 图片的原地址（Telegram CDN地址）
            "proxy_url": "https://api.skyju.cc/mobile-acg/api.php?method=get&id=5478" # 反代地址
        },
        {
            "id": 8246,
            "raw_url": "https://cdn1.telesco.pe/file/DXC1...3g.jpg",
            "proxy_url": "https://api.skyju.cc/mobile-acg/api.php?method=get&id=8246"
        }
    ]
}
```

### 反代获取一张随机或指定图片的数据

| GET参数 | 值类型        | 是否可选 | 说明                     |
| ------- | ------------- | -------- | ------------------------ |
| method  | "get": String | 否       | 本接口规定的method值     |
| id      | Int           | 是       | 图片的ID；不指定则为随机 |

示例请求：

```bash
curl "https://api.skyju.cc/mobile-acg/api.php?method=get&id=1000" -o image.jpg
curl "https://api.skyju.cc/mobile-acg/api.php?method=get" -o image.jpg # 随机获取
```

若成功，返回`image/jpeg`数据；若失败，返回JSON数据。

### 随机获取一张图片并跳转到地址

| GET参数  | 值类型                     | 是否可选 | 说明                                               |
| -------- | -------------------------- | -------- | -------------------------------------------------- |
| method   | "redirect": String         | 否       | 本接口规定的method值                               |
| no_proxy | 此参数无需值，只需键名存在 | 是       | 若存在此参数，表示跳转到Telegram原地址而非反代地址 |

示例请求：

```bash
curl -v "https://api.skyju.cc/mobile-acg/api.php?method=redirect"
```

示例返回：

```
...
HTTP/1.1 302 Found
Location: https://api.skyju.cc/mobile-acg/api.php?method=get&id=4137
...
```

示例请求：

```bash
curl -v "https://api.skyju.cc/mobile-acg/api.php?method=redirect&no_proxy"
```

示例返回：

```
...
HTTP/1.1 302 Found
Location: https://cdn4.telesco.pe/file/UXncB4Lo...5Qv4kyA.jpg
...
```

## 在VPS上部署本项目

1.切换到你的站点目录：

```bash
cd /path/to/your/www/
```

2.拉取本项目：

```bash
git clone https://github.com/juzeon/mobile-acg.git mobile-acg
```

3.添加cron任务每日从Telegram更新数据库：

```bash
0 1 * * * php /path/to/your/www/mobile-acg/update-cli.php # 每日凌晨一点更新
```

4.访问你的网站相应地址，检查API可用性。