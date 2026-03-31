#!/bin/bash

# 出租车智能监控系统 - 服务器一键部署脚本
# 在服务器上运行此脚本，自动完成 Nginx 配置

echo "=========================================="
echo "  出租车智能监控系统 - 服务器部署"
echo "=========================================="
echo ""

# 检查是否以 root 运行
if [ "$EUID" -ne 0 ]; then 
    echo "❌ 请使用 root 权限运行此脚本"
    echo "使用命令: sudo bash deploy-to-server.sh"
    exit 1
fi

# 获取服务器 IP
SERVER_IP=$(curl -s ifconfig.me)
if [ -z "$SERVER_IP" ]; then
    SERVER_IP=$(hostname -I | awk '{print $1}')
fi

echo "🖥️  检测到服务器 IP: $SERVER_IP"
echo ""

# 提示用户输入配置
read -p "📁 请输入前端文件路径 (默认: /www/wwwroot/taxi-monitor/dist): " DIST_PATH
DIST_PATH=${DIST_PATH:-/www/wwwroot/taxi-monitor/dist}

read -p "🔌 请输入后端服务端口 (默认: 8080): " BACKEND_PORT
BACKEND_PORT=${BACKEND_PORT:-8080}

echo ""
echo "配置信息："
echo "  - 服务器 IP: $SERVER_IP"
echo "  - 前端路径: $DIST_PATH"
echo "  - 后端端口: $BACKEND_PORT"
echo ""
read -p "确认配置无误？(y/n): " CONFIRM

if [ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ]; then
    echo "❌ 部署已取消"
    exit 0
fi

echo ""
echo "=========================================="
echo "  开始部署..."
echo "=========================================="
echo ""

# 1. 检查并安装 Nginx
echo "📦 检查 Nginx..."
if ! command -v nginx &> /dev/null; then
    echo "⚠️  Nginx 未安装，开始安装..."
    
    if command -v yum &> /dev/null; then
        # CentOS/RHEL
        yum install -y nginx
    elif command -v apt &> /dev/null; then
        # Ubuntu/Debian
        apt update
        apt install -y nginx
    else
        echo "❌ 无法识别的系统类型，请手动安装 Nginx"
        exit 1
    fi
    
    if [ $? -eq 0 ]; then
        echo "✅ Nginx 安装成功"
    else
        echo "❌ Nginx 安装失败"
        exit 1
    fi
else
    echo "✅ Nginx 已安装"
fi

# 2. 启动 Nginx
echo ""
echo "🚀 启动 Nginx..."
systemctl start nginx
systemctl enable nginx

if systemctl is-active --quiet nginx; then
    echo "✅ Nginx 运行中"
else
    echo "❌ Nginx 启动失败"
    exit 1
fi

# 3. 创建配置文件
echo ""
echo "📝 创建 Nginx 配置..."

NGINX_CONF="/etc/nginx/conf.d/taxi-monitor.conf"

cat > "$NGINX_CONF" << EOF
# 出租车智能监控系统 - Nginx 配置
# 自动生成于: $(date)

server {
    listen 80;
    server_name $SERVER_IP;
    
    root $DIST_PATH;
    index index.html;
    
    charset utf-8;
    
    access_log /var/log/nginx/taxi-monitor-access.log;
    error_log /var/log/nginx/taxi-monitor-error.log;
    
    # Gzip 压缩
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript 
               application/json application/javascript application/xml+rss;
    
    # 前端路由
    location / {
        try_files \$uri \$uri/ /index.html;
        
        # 静态资源缓存
        location ~* \\.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)\$ {
            expires 30d;
            add_header Cache-Control "public, immutable";
        }
    }
    
    # 后端 API 代理
    location /api/ {
        proxy_pass http://localhost:$BACKEND_PORT/api/;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        
        proxy_connect_timeout 300s;
        proxy_send_timeout 300s;
        proxy_read_timeout 300s;
    }
    
    # 禁止访问隐藏文件
    location ~ /\\. {
        deny all;
    }
}
EOF

echo "✅ 配置文件已创建: $NGINX_CONF"

# 4. 测试配置
echo ""
echo "🧪 测试 Nginx 配置..."
nginx -t

if [ $? -ne 0 ]; then
    echo "❌ Nginx 配置测试失败"
    exit 1
fi

echo "✅ 配置测试通过"

# 5. 重载 Nginx
echo ""
echo "🔄 重载 Nginx..."
systemctl reload nginx

if [ $? -eq 0 ]; then
    echo "✅ Nginx 重载成功"
else
    echo "❌ Nginx 重载失败"
    exit 1
fi

# 6. 检查文件
echo ""
echo "📂 检查前端文件..."
if [ -f "$DIST_PATH/index.html" ]; then
    echo "✅ 前端文件存在"
else
    echo "⚠️  警告: 前端文件不存在，请上传 dist 文件夹到 $DIST_PATH"
fi

# 7. 设置权限
echo ""
echo "🔐 设置文件权限..."
if [ -d "$DIST_PATH" ]; then
    chown -R nginx:nginx $(dirname "$DIST_PATH")
    chmod -R 755 $(dirname "$DIST_PATH")
    echo "✅ 权限设置完成"
fi

# 8. 配置防火墙
echo ""
echo "🔥 配置防火墙..."

if command -v firewall-cmd &> /dev/null; then
    # firewalld (CentOS 7+)
    firewall-cmd --permanent --add-service=http
    firewall-cmd --permanent --add-service=https
    firewall-cmd --reload
    echo "✅ 防火墙已配置 (firewalld)"
elif command -v ufw &> /dev/null; then
    # ufw (Ubuntu)
    ufw allow 80/tcp
    ufw allow 443/tcp
    echo "✅ 防火墙已配置 (ufw)"
else
    echo "⚠️  未检测到防火墙，请手动开放 80/443 端口"
fi

# 9. 检查后端服务
echo ""
echo "🔍 检查后端服务..."
if netstat -tulnp | grep -q ":$BACKEND_PORT"; then
    echo "✅ 后端服务运行中 (端口 $BACKEND_PORT)"
else
    echo "⚠️  警告: 后端服务未运行，请先启动后端服务"
fi

echo ""
echo "=========================================="
echo "  ✅ 部署完成！"
echo "=========================================="
echo ""
echo "📌 访问地址："
echo "   http://$SERVER_IP"
echo ""
echo "🔑 登录信息："
echo "   账号: admin"
echo "   密码: 123456"
echo ""
echo "📁 文件位置："
echo "   前端: $DIST_PATH"
echo "   配置: $NGINX_CONF"
echo "   日志: /var/log/nginx/taxi-monitor-*.log"
echo ""
echo "🔧 常用命令："
echo "   查看状态: systemctl status nginx"
echo "   重载配置: systemctl reload nginx"
echo "   查看日志: tail -f /var/log/nginx/taxi-monitor-error.log"
echo ""
echo "=========================================="

