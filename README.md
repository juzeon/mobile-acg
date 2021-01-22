# mobile-acg

![](https://badgen.net/badge/PHP/%3E=7.1/blue)
![](https://badgen.net/badge/php_extension/pdo_sqlite/blue)
![](https://badgen.net/badge/license/MIT/green)

一个PHP开发的HTTP API，随机获取一张或多张来自Telegram频道 [t.me/MikuArt](https://t.me/MikuArt) 的适合竖屏移动设备查看的二次元图片，并上传到今日头条的图床，支持JSON获取地址或直接跳转。

网络上其实有很多类似的API，但基本都不开源，不能自行部署，服务稳定性没有保障。并且许多接口内置的图片较少，或者很久都不再更新了。所以我就自己写了一个。调用的是Telegram地址，上传到今日头条的图床，并且可以自行部署，服务稳定性有保障。另外Telegram频道@MikuArt这个频道存在时间很长了，每天都维持着一个很高的更新频率。

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
    "status": true,
    "data": [
        {
            "id": 9824,
            "url": "https://img11.360buyimg.com/ddimg/jfs/t1/157690/25/4215/76309/600a8e64Ec3ca56f7/f41912b4f6a6be9f.jpg"
        },
        {
            "id": 9028,
            "url": "https://img14.360buyimg.com/ddimg/jfs/t1/157866/6/4622/87677/600a8f73E3c156e23/b20bdc78aed0212d.jpg"
        }
    ]
}
```

### 随机或指定ID获取一张图片并跳转到地址

| GET参数 | 值类型        | 是否可选 | 说明                       |
| ------- | ------------- | -------- | -------------------------- |
| method  | "get": String | 否       | 本接口规定的method值       |
| id      | Int           | 是       | 图片的ID；不指定为随机获取 |

示例请求：

```bash
curl -v "https://api.skyju.cc/mobile-acg/api.php?method=get"
```

示例返回：

```
...
HTTP/1.1 302 Found
Location: https://p.pstatp.com/origin/1384d00016e9a0aa34dae
...
```

示例请求：

```bash
curl -v "https://api.skyju.cc/mobile-acg/api.php?method=get&id=9876"
```

示例返回：

```
...
HTTP/1.1 302 Found
Location: https://p.pstatp.com/origin/1384e00040d776e8f2486
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

3.添加cron任务每日从Telegram更新图片，上传并存储到数据库：

```bash
0 1 * * * php /path/to/your/www/mobile-acg/update-cli.php # 每日凌晨一点更新
```

4.访问你的网站相应地址，检查API可用性。