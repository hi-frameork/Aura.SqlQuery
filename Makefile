.PHONY: help
help:
	@echo ""
	@printf "\033[33m%s:\033[0m\n" '使用说明'
	@sed -n 's/^##//p' ${MAKEFILE_LIST} | column -t -s ':' |  sed 's/^/ /'
	@echo ""

## shell: 进入当前服务容器内
.PHONY: shell
shell: info
	@sh tests/make.sh shell

## tests: 运行单元测试
.PHONY: tests
tests: info
	@sh tests/make.sh tests

## cs: 代码优化
.PHONY: cs
cs: info
	@echo '# Code style format'
	@sh tests/make.sh cs

## check: 编码检查
.PHONY: check
check: info
	@echo '# Code check'
	@sh tests/make.sh check

## update: cmposer update
.PHONY: update
update: info
	@echo '# 更新依赖包 vender'
	@sh tests/make.sh update

## install: cmoposer install
.PHONY: install
install: info
	@echo '# 安装依赖包 vender'
	@sh tests/make.sh install

# 打印环境信息
info:
	@echo '- basedir:' $(shell pwd)
	@echo '- os:     ' $(shell uname | awk '{print tolower($$0)}')
	@echo '- arch:   ' $(shell uname -m)
	@echo ""
