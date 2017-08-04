#!/bin/bash

export AWS_ACCESS_KEY_ID=`cat ~/.aws/credentials | grep _id | awk '{print $3}'`
echo $AWS_ACCESS_KEY_ID

export AWS_SECRET_ACCESS_KEY=`cat ~/.aws/credentials | grep secret | awk '{print $3}'`
echo $AWS_SECRET_ACCESS_KEY


