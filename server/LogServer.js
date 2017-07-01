/*导入需要用到的nodejs库*/
var http = require('http');
var url  = require('url');
var qs   = require('querystring');
var ws   = require('ws');

var clientList = {};
/**
 * 简单配置个路由 用来检测无用的请求 仅符合路由规则的才能被接受
 * 自己可以按照需要定义
 * @type {{/: string, favicon: string, user: string, login: string, biz: string}}
 */
var route = {
    '/': "/",
    'favicon': '/favicon.ico',
    'user': '/user',
    'login': '/user/login',
    'biz': '/biz'
};

/**
 * 上述路由的简单判断规则
 * @param reqPath
 * @returns {boolean}
 */
var isValid = function (reqPath) {
    for (var key in route) { if (route[key] == reqPath) { return true;} }
    return false;
}

/**
 * 启用http
 */
http.createServer(function (req, res) {

    if (!isValid(url.parse(req.url).pathname)) {
        res.writeHead(404, {'Content-Type': 'text/plain;charset=utf-8'});
        res.write("{'errcode':404,'errmsg':'404 页面不见啦'}");
        res.end();
    }
    else {
        res.writeHead(200, {'Content-Type': 'text/plain;charset=utf-8'});
        if (req.method.toUpperCase() == 'POST') {
            var postData = "";
            req.addListener("data", function (data) {
                postData += data;
            });

            req.addListener("end", function () {
                var query = qs.parse(postData);
                onRequest(query ,res);
            });
        }
        else if (req.method.toUpperCase() == 'GET') {
            /**
             * 也可使用var query=qs.parse(url.parse(req.url).query);
             * 区别就是url.parse的arguments[1]为true：
             * ...也能达到‘querystring库’的解析效果，而且不使用querystring
             */
            var query = url.parse(req.url, true).query;
            onRequest(query ,res);
        } else {
            //head put delete options etc.
        }
    }

}).listen(8080, function () {
    console.log("listen on port 8080");
});


/***
 * 接受并认证 调试信息 .
 * @param queryParam
 * @param response
 */
function  onRequest(queryParam , response) {
    console.log('Received-http:',queryParam);
    var token =  queryParam.token;

   if(clientList[token])
   {
       clientList[token].send( JSON.stringify( queryParam ));
       response.write( JSON.stringify( {'status':1,'msg':'调试信息发送成功'} ));
       response.end();
   }else{
       response.write( JSON.stringify( {'status':0,'msg':'调试客户端未就绪,请在浏览器上初始化这个token!'} ));
       response.end();
   }

}

const  webServer = new ws.Server({
    perMessageDeflate: false,
    port: 4000
});


//处理客户端连接
webServer.on('connection', function connection(client) {
    console.log('新的客户端');
    var token = '';
    client.on('message', function incoming(message) {
        console.log('Received-ws:', message);
        var  msg = JSON.parse(message);
        if(msg.type == 'token')
        {
            if(!msg.data)
            {
                client.send(JSON.stringify( { 'type':'sys' ,'msg':'令牌不能为空' } ));
                return;
            }

            token = msg.data;
            if(clientList[token])
            {
                client.send(JSON.stringify( { 'type':'sys' ,'msg':'认证失败,令牌已经存在' } ));
            }else{
                clientList[token] = client;
                client.send(JSON.stringify( { 'type':'sys' ,'msg':'认证成功' } ));
            }
        }else
        {
            client.send(JSON.stringify( { 'type':'sys' ,'msg':'未定义请求' } ));
        }
    });

    // 用户断开连接
    client.on('close' , function () {
        console.log("Client close! ");
        delete  clientList[token];
    });

});
