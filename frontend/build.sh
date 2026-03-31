#!/bin/bash

# 出租车智能监控系统 - 前端打包脚本
# 适用于 Linux/Mac 系统

echo "=========================================="
echo "  出租车智能监控系统 - 前端打包"
echo "=========================================="
echo ""

# 检查 Node.js 是否安装
if ! command -v node &> /dev/null
then
    echo "❌ 错误: Node.js 未安装"
    echo "请先安装 Node.js (建议版本: 16+)"
    exit 1
fi

echo "✅ Node.js 版本: $(node -v)"
echo "✅ npm 版本: $(npm -v)"
echo ""

# 安装依赖
echo "📦 开始安装依赖..."
npm install

if [ $? -ne 0 ]; then
    echo "❌ 依赖安装失败"
    exit 1
fi

echo "✅ 依赖安装成功"
echo ""

# 执行打包
echo "🔨 开始打包项目..."
npm run build

if [ $? -ne 0 ]; then
    echo "❌ 打包失败"
    exit 1
fi

echo ""
echo "=========================================="
echo "  ✅ 打包成功！"
echo "=========================================="
echo ""
echo "📁 打包文件位置: $(pwd)/dist"
echo "📦 打包文件大小: $(du -sh dist | cut -f1)"
echo ""
echo "🚀 部署步骤："
echo "1. 将 dist 文件夹上传到服务器"
echo "2. 配置 Nginx 指向 dist 目录"
echo "3. 访问服务器 IP 地址"
echo ""
echo "详细部署说明请查看: 前端部署说明.md"
echo "=========================================="

