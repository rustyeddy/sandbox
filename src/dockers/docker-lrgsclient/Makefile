#
# This is the Makefile
#
last-build: Dockerfile
	docker build -t rustyeddy/lrgs .
	touch last-build

run: last-build
	docker run --rm -it --name lrgs rustyeddy/lrgs
