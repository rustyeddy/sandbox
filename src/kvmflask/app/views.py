from flask import render_template
from app import app

import libvirt

@app.route('/')
@app.route('/index')
@app.route('/home')
def index():
    user = {'nickname': 'Rusty'}
    conn = libvirt.open("qemu:///system")
    domains = conn.listDefinedDomains()

    dlist = []
    for d in domains:
        dlist.append({"name": d})

    return render_template('index.html',
                           user='user',
                           title='Home',
                           domains=dlist)
