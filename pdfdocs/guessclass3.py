import json
import pymysql
import re
#from sklearn import tree
#clf             = tree.DecisionTreeClassifier()
rx = []
rx.append("\\b(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]{0,6}(-|\\/| |,){1,2}(0|1|2|3)?[0-9](st|nd|rd|th)?(-|\\/| |,){1,2}(19|20)?(16|17|18|19)\\b")
rx.append("\\b(0|1|2|3)?\\d(st|nd|rd|th)?(-|\\/| |,){1,2}(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]{0,6}(st|nd|rd|th)?(-|\\/| |,){1,2}(19|20)?(16|17|18|19)\\b")
rx.append("\\b(0|1|2|3)?\\d ?(-|\\/|,|\\.){1,2} ?(0|1|2)? ?\\d ?(-|\\/|,|\\.){1,2} ?(19|20)?(16|17|18|19|20|21|22|23)\\b")
vrange = (-8,-7,-6,-5,-4,-3,-2,-1,1,2,3,4)
dic = set()
excludes = ['2016','2017','2018','2019','SP2','07152']

db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
c = db.cursor()

e = c.execute("select * from apdocs where posted is not null and supplierid = '61215' order by editdate")
precoded = c.fetchall()

e = c.execute("select * from apdocs where posted is null and itemid = 538804")

uncoded = c.fetchall()

c.close()
db.close()


def makepagebigrams(txt):
 newset = set()
 pages = txt.split("\f")
 lastword = ''
 t = pages[0].split()
 wdset = filter(lambda x:len(x)>=5 and not(re.search('\d+',x)),t[0:20]+t[-20:])
 for w in wdset:
  newset.add('1_'+lastword+"_"+w)
  lastword = w
 lastword = ''
 t = pages[1].split()
 wdset = filter(lambda x:len(x)>=5 and not(re.search('\d+',x)),t[0:20]+t[-20:])
 for w in wdset:
  newset.add('2_'+lastword+"_"+w)
  lastword = w
 return newset

def makebigrams(pdftext):
 wins = 0
 words = pdftext.split()[0:120]
 lastword = ''
 wordset = set()
 for w in words:
  if wins>60:
   continue
  if len(w) < 5:
   continue
  if re.search('\d',w):
   continue
  wordset.add(lastword+'_'+w)
  wins += 1
  lastword = w
 words = pdftext.split()[-20:]
 for w in words:
  lastword = ''
  if len(w) < 5:
   continue
  if re.search('\d',w):
   continue
  wordset.add(lastword+'_'+w)
  wins += 1
  lastword = w
 return wordset

def guessclass(wordset,dicindex):
 bestarray = {'0':0,'1':0,'2':0,'3':0,'4':0,'5':0,'6':0,'7':0,'8':0,'9':0,'10':0,'11':0,'12':0,'13':0,'14':0,'15':0,'16':0,'17':0,'18':0,'19':0}
 for word in wordset:
  if word in dicindex:
   for clas in dicindex[word]:
    bestarray[clas] = 1 if clas not in bestarray else bestarray[clas] + 1
 best = max(bestarray, key=bestarray.get)
 s    = sorted(bestarray.values())[-8:]
 return best,s
 
def makeclasscorpus():
 classcorpus = {}
 for rec in precoded:
  wordset = makebigrams(rec[9])
  for word in wordset:
   classcorpus.setdefault(word,set()).add(str(rec[12])+'|'+str(rec[13]))
 return classcorpus

def stampsplits():
 db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
 c = db.cursor()
 e = c.execute("select itemid,textfromfile from apdocs where pageto>pagefrom and pageto is not null")
 dontsplit = c.fetchall()
 e = c.execute("SELECT a.itemid,b.itemid,a.textfromfile,b.textfromfile FROM apdocs a, apdocs b WHERE a.emailuid = b.emailuid AND b.itemid > a.itemid and a.textfromfile is not null and b.textfromfile is not null")
 dosplit = c.fetchall()
 e = c.execute("select itemid,textfromfile,pages,filename from apdocs where pages > 1 and pageto is null and textfromfile is not null")
 unsplit = c.fetchall()

 splitcorpus = {}
 for rec in dontsplit:
  for p in range(0,len(rec[1].split("\f"))-1):
   s = makepagebigrams(rec[1].split("\f")[p]+"\f"+rec[1].split("\f")[p+1])
   for w in s:
    splitcorpus.setdefault(w,set()).add('dontsplit')
 for rec in dosplit:
  pages = rec[2].split("\f")[-1]+"\f"+rec[3].split("\f")[0]
  s = makepagebigrams(pages)
  for w in s:
   splitcorpus.setdefault(w,set()).add('split')

 for rec in unsplit:
  ps = []
  for p in range(0,len(rec[1].split("\f"))-1):
   s = makepagebigrams(rec[1].split("\f")[p]+"\f"+rec[1].split("\f")[p+1])
   ans,li = guessclass(s,splitcorpus)
   if ans == 'split':
    ps.append(p+1)
  sql = "update apdocs set otherjson=JSON_SET(otherjson,'$.ps',JSON_ARRAY("+','.join(map(str,ps))+")) where itemid = '"+str(rec[0])+"'"
  print(sql)
  a = c.execute(sql)
  db.commit()
 c.close()
 db.close()


def getallkeys(targetpos,targetlength,splittextlength,splittext):
 allkeys = {}
 for v in vrange:
  anchorpos = v+targetpos+((v>0)*targetlength)
  if anchorpos>=0 and anchorpos<splittextlength:
   offsetword = splittext[anchorpos]
   if not(re.match('.*\\d.*',offsetword)):
    k = offsetword+'|'+str(targetlength)+'|'+str(v)
    allkeys[k] = allkeys[k]+1 if k in allkeys else 1
 return allkeys

def makefeatdic():
 dic = {}
 lastrec = {}
 for rec in precoded:  
  if rec[13] == 'C' or rec[13] == 'I':
   text = rec[9]
   dicid = str(rec[12])+'|'+rec[13]
   splittext = text.split()
   lastrec[dicid] = splittext
   splittextlength = len(splittext)
   # invoice purchaseorder taxdate goods vat total
   json23 = json.loads(rec[23])
   features = {15:rec[15],16:rec[16],17:json23.get('f_17',[[]])[0],18:json23.get('f_18',[[]])[0],19:json23.get('f_19',[[]])[0],20:json23.get('f_20',[[]])[0]}

   for featureid,targetfeature in features.items():
    if targetfeature is None:
     continue
    targetfeature = str(targetfeature)
    if len(targetfeature) < 2:
     continue
    targetlength = len(targetfeature.split())
    p = text.find(targetfeature,0)
    while p >= 0:
     tempx = len(text[:p+1].split())
     print(p,targetfeature,tempx-1,targetlength,'b',splittext[tempx-1],splittext[tempx],splittext[tempx+1],rec[0])
     k = getallkeys(len(text[:p+1].split())-1,targetlength,splittextlength,splittext)
     for x in k:
      nk =dicid+'|'+str(featureid)+'|'+x 
      dic[nk] = dic[nk]+1 if nk in dic else 1
     p = text.find(targetfeature,p+1)
# for v in dic:
#  print(v,dic[v])
 return dic,lastrec

def stampfeatures():
 db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
 c = db.cursor()

 for rec in uncoded:
  text = rec[9]
  if text is None:
   continue
  wordset = makebigrams(text)
  bestclass,bestclassarray = guessclass(wordset,classcorpus)
  dicid = bestclass
  splittext = text.split()
  splittextlength = len(splittext)
  bests = {15:{},16:{},17:{},18:{},19:{},20:{}}
  for targetlength in range(0,4):
   for p in range(0,splittextlength-targetlength):
    poskeys = getallkeys(p,targetlength+1,splittextlength,splittext)
    targetword = ''.join(splittext[p:p+targetlength+1])
    targetkey = str(p)+'|'+str(targetlength+1)+'|'+targetword
    # Invoice No.
    if re.match('.*\\d.*',targetword):
     # Also exclude anything found on the last invoice.
     cnt = 0
     for v in poskeys:
      cnt += dic[dicid+'|15|'+v] if dicid+'|15|'+v in dic else 0
     bests[15][targetkey] = cnt
    # Purchase Order
    if re.match('.*\\d.*',targetword):
     cnt = 0
     for v in poskeys:
      cnt += dic[dicid+'|16|'+v] if dicid+'|16|'+v in dic else 0
     bests[16][targetkey] = cnt
    # Date
    for r in rx:
     if re.match(r,targetword):
      cnt = 0
      for v in poskeys:
       cnt += dic[dicid+'|17|'+v] if dicid+'|17|'+v in dic else 0
      bests[17][targetkey] = cnt
    # Goods
    if re.match('^.?\\d{0,3},?\\d{1,3}\.\\d\\d$',targetword):
     cnt = 0
     for v in poskeys:
      cnt += dic[dicid+'|18|'+v] if dicid+'|18|'+v in dic else 0
     bests[18][targetkey] = cnt
    # VAT
    if re.match('^.?\\d{0,3},?\\d{1,3}\.\\d\\d$',targetword):
     cnt = 0
     for v in poskeys:
      cnt += dic[dicid+'|19|'+v] if dicid+'|19|'+v in dic else 0
     bests[19][targetkey] = cnt
    # Total
    if re.match('^.?\\d{0,3},?\\d{1,3}\.\\d\\d$',targetword):
     cnt = 0
     for v in poskeys:
      cnt += dic[dicid+'|20|'+v] if dicid+'|20|'+v in dic else 0
     bests[20][targetkey] = cnt


  supplierid,doctype = dicid.split('|')
  sql = "update apdocs set supplierid = '"+supplierid+"', doctype='"+doctype+"' where itemid = '"+str(rec[0])+"'"
  a = c.execute(sql)
  db.commit()
  
  for b in bests:
   if len(bests[b])>0:
    best = sorted(bests[b],key=bests[b].get,reverse=True)
    targetpos = int(best[0].split('|')[0])
    targetlength = int(best[0].split('|')[1])
    v = " ".join(splittext[targetpos:targetpos+targetlength])
    sql = "update apdocs set otherjson=JSON_SET(otherjson,'$.f_"+str(b)+"',\""+v+"\") where itemid = '"+str(rec[0])+"'"
    a = c.execute(sql)
    db.commit()

 c.close()
 db.close()
    

def stampinvoiceno():
 db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
 c = db.cursor()
 sql = "update apdocs set otherjson=JSON_SET(otherjson,'$.f_15',\""+str(best[0])+"\") where itemid = '"+str(rec[0])+"'"
 a = c.execute(sql)
 db.commit()
 c.close()
 db.close()


#stampsplits()
classcorpus = makeclasscorpus()
dic,lastrec = makefeatdic()
stampfeatures()


#stampclass(classcorpus)