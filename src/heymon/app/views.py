from app import app

import psutil

@app.route('/')
@app.route('/index')

def index():
	print ("Hello")
	foo = psutil.cpu_times()
	print(foo);

