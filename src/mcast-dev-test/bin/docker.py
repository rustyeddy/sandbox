#!/usr/bin/python




from docker import Client

function getDockerSocket():
	c = Client(base_url='unix://var/run/docker.sock')
	return c
	
