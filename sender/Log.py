import urllib
import urllib2

#python debug file ,test at python27
class  Log:
  serverURL = "http://17ky.xyt:8080"
  token  = 'xytschool'
  forbidden = False
  error = ''
  def  setServer(self ,url):
       self.serverURL = url

  def  setToken(self,  token):
       self.token= token

  def  send(self , type,content ,gruop):
       frame  = {'token': self.token,
                 'type':type ,
                 'group':gruop,
                 'data':content,
                 'contentType':'text'}
       return self._send(self.serverURL , frame )

  def _send(self , requrl ,frame ):
        _frame = urllib.urlencode(frame)
        req    =  urllib2.Request(url = requrl,data =_frame)
        res_data = urllib2.urlopen(req)
        res      = res_data.read()
        return res


  def  info(self ,content ,group='all'):
       return self.send('info',content,group)

  def  waring(self ,content ,group='all'):
       return self.send('waring',content,group)

  def  error(self ,content ,group='all'):
       return self.send('error',content,group)

#test 
log = Log()

res = log.waring('run at lin 43')
res = log.info('run at lin 44')
res = log.error('run at lin 45')
res = log.waring('run at lin 43' ,'group1')
res = log.info('run at lin 44' ,'group2')
res = log.error('run at lin 45' ,'group1')
print(res)

