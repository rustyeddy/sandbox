FROM ubuntu:xenial
MAINTAINER Rusty Eddy <rusty@rustyeddy.com>

#
# For the complete development package install openjdk-8-jdk
# 
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    unzip \
    openjdk-8-jre \
    && rm -rf /var/lib/apt/lists/*

