VLC Configuration to stream and video
=====================================


```
:sout=#transcode{vcodec=h264,acodec=mpga,ab=128,channels=2,samplerate=44100}:duplicate{dst=rtp{dst=227.1.1.1,port=5004,mux=ts,sap,name=mw},dst=display} :sout-all :sout-keep
```
