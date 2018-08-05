import json
import pymysql
import re
#from sklearn import tree
#clf             = tree.DecisionTreeClassifier()
rx = []
rx.append("\\b(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]{0,6}(-|\\/| |,){1,2}(0|1|2|3)?[0-9](st|nd|rd|th)?(-|\\/| |,){1,2}(19|20)?(16|17|18|19)\\b")
rx.append("\\b(0|1|2|3)?\\d(st|nd|rd|th)?(-|\\/| |,){1,2}(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]{0,6}(st|nd|rd|th)?(-|\\/| |,){1,2}(19|20)?(16|17|18|19)\\b")
rx.append("\\b(0|1|2|3)?\\d ?(-|\\/|,|\\.){1,2} ?(0|1|2)? ?\\d ?(-|\\/|,|\\.){1,2} ?(19|20)?(16|17|18|19)\\b")

db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
c = db.cursor()

e = c.execute("select * from apdocs where posted is not null order by editdate")
precoded = c.fetchall()

e = c.execute("select * from apdocs where posted is null")
uncoded = c.fetchall()

c.close()
db.close()

def makebigrams(pdftext):
 wins = 0
 words = pdftext.split()[0:80]
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
   classcorpus.setdefault(word,set()).add(str(rec[12])+'_'+str(rec[13]))
 return classcorpus

def stampclass(classcorpus):
 db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
 c = db.cursor()
 for rec in uncoded:
  wordset = makebigrams(rec[9])
  best,bestarray = guessclass(wordset,classcorpus)
  if max(bestarray)>10:
   supplierid,doctype = best.split('_')
   sql = "update apdocs set supplierid = '"+supplierid+"', doctype='"+doctype+"' where itemid = '"+str(rec[0])+"'"
   a = c.execute(sql)
   db.commit()
 c.close()
 db.close()

vrange = (-11,-10,-9,-8,-7,-6,-4,-3,-2,-1,1,2,3)
dic = set()
gendic = set()
excludes = ['2016','2017','2018','2019','SP2','07152']

def makefeatdic():
 dic = set()
 gendic = set()
 lastrec = {}
 maxp = 10
 for rec in precoded:  
  if rec[13] == 'C' or rec[13] == 'I':
   text = rec[9]
   supplierid = str(rec[12])
   splittext = text.split()
   lastrec[supplierid+' '+rec[13]] = splittext
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
    if targetfeature[-4:] == '0.00':
     continue
    targetfeaturelength = len(targetfeature.split())-1
    p = text.find(targetfeature,0)
    pcount = 0
    while p >= 0 and pcount < maxp:
     targetpos = len(text[:p+1].split())-1
     pcount += 1
     p = text.find(targetfeature,p+1)
     for v in vrange:
      anchorpos = targetpos+v+((v>0)*targetfeaturelength)
      if anchorpos >= 0 and anchorpos < splittextlength:
       word = splittext[anchorpos]
       if re.search('\\d',word):
        continue
       key = supplierid+' '+word+' '+str(v)+' '+str(featureid)
       dic.add(key)
       key = word+' '+str(v)+' '+str(featureid)
       gendic.add(key)
 return dic,lastrec

def datecandidates(rec):
 ans = {}
 t = rec[9]
 for r in rx:
  for v in re.finditer(r,t,re.IGNORECASE):
   ans[len(t[:v.start(0)].split())] = v.group(0)
 return ans

def currencycandidates(rec):
 ans = {}
 for wordpos,word in enumerate(rec[9].split()):
  if re.match('^.*\\d\.\\d\\d$',word):
   ans[wordpos] = word;
 return ans

def invoicecandidates(rec):
 ans = {}
 for wordpos,word in enumerate(rec[9].split()):
  if not(re.match('.*\\d+.*',word)):
    continue
  if re.match('\\d+\\.\\d\\d',word):
   continue
  if word in ['2016','2017','2018','2019','2020','SP2']:
   continue
  if len(word)<3:
   continue
  if re.match('\\d{1,2}\\/\\d\\d\\/\\d\\d',word):
   continue
  classid = str(rec[12])+' '+str(rec[13])
  if classid in lastrec:
   if word in lastrec[classid]:
    continue
  ans[wordpos] = word;
 return ans

def pocandidates(rec):
 ans = {}
 for wordpos,word in enumerate(rec[9].split()):
  if re.match('\\d+\\.\\d\\d',word):
   continue
  if word in ['2016','2017','2018','2019','2020','SP2']:
   continue
  if len(word)<3:
   continue
  ans[wordpos] = word;
 return ans

def stampinvoiceno():
 db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
 c = db.cursor()
 for rec in uncoded:
  supplierid = str(rec[12])
  text = rec[9]
  splittext = text.split()
  splittextlength = len(text.split())
  bests = {}
  for wordpos,word in invoicecandidates(rec).items():
   targetfeaturelength = len(word.split())-1
   for v in vrange:
    anchorpos = wordpos+v+((v>0)*targetfeaturelength)
    if anchorpos >= 0 and anchorpos < splittextlength:
     w = splittext[anchorpos]
     key = supplierid+' '+w+' '+str(v)+' 15'
     if key in dic:
      bests[word] = bests[word]+1 if word in bests else 1
  if len(bests)>0:
   best = sorted(bests,key=bests.get,reverse=True)
   if len(best)>0:
    sql = "update apdocs set otherjson=JSON_SET(otherjson,'$.f_15',\""+str(best[0])+"\") where itemid = '"+str(rec[0])+"'"
    a = c.execute(sql)
    db.commit()
 c.close()
 db.close()



def stamppo():
 db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
 c = db.cursor()
 for rec in uncoded:
  supplierid = str(rec[12])
  text = rec[9]
  splittext = text.split()
  splittextlength = len(text.split())
  bests = {}
  for wordpos,word in pocandidates(rec).items():
   targetfeaturelength = len(word.split())-1
   for v in vrange:
    anchorpos = wordpos+v+((v>0)*targetfeaturelength)
    if anchorpos >= 0 and anchorpos < splittextlength:
     w = splittext[anchorpos]
     key = supplierid+' '+w+' '+str(v)+' 16'
     if key in dic:
      bests[word] = bests[word]+1 if word in bests else 1
  if len(bests)>0:
   best = sorted(bests,key=bests.get,reverse=True)
   if len(best)>0:
    sql = "update apdocs set otherjson=JSON_SET(otherjson,'$.f_16',\""+str(best[0])+"\") where itemid = '"+str(rec[0])+"'"
    a = c.execute(sql)
    db.commit()
 c.close()
 db.close()




def stamptaxdates():
 db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
 c = db.cursor()
 for rec in uncoded:
  supplierid = str(rec[12])
  text = rec[9]
  splittext = text.split()
  splittextlength = len(text.split())
  bests = {}
  for wordpos,word in datecandidates(rec).items():
   targetfeaturelength = len(word.split())-1
   for v in vrange:
    anchorpos = wordpos+v+((v>0)*targetfeaturelength)
    if anchorpos >= 0 and anchorpos < splittextlength:
     w = splittext[anchorpos]
     key = supplierid+' '+w+' '+str(v)+' 17'
     if key in dic:
      bests[word] = bests[word]+1 if word in bests else 1
  if len(bests)>0:
   best = sorted(bests,key=bests.get,reverse=True)
   if len(best)>0:
    sql = "update apdocs set otherjson=JSON_SET(otherjson,'$.f_17',\""+str(best[0])+"\") where itemid = '"+str(rec[0])+"'"
    a = c.execute(sql)
    db.commit()
 c.close()
 db.close()

def stamptotals():
 db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
 c = db.cursor()
 for rec in uncoded:
  supplierid = str(rec[12])
  text = rec[9]
  splittext = text.split()
  splittextlength = len(text.split())
  bests = {}
  for wordpos,word in currencycandidates(rec).items():
   targetfeaturelength = len(word.split())-1
   for v in vrange:
    anchorpos = wordpos+v+((v>0)*targetfeaturelength)
    if anchorpos >= 0 and anchorpos < splittextlength:
     w = splittext[anchorpos]
     key = supplierid+' '+w+' '+str(v)+' 20'
     if key in dic:
      bests[word] = bests[word]+1 if word in bests else 1
  if len(bests)>0:
   best = sorted(bests,key=bests.get,reverse=True)
   if len(best)>0:
    sql = "update apdocs set otherjson=JSON_SET(otherjson,'$.f_20',\""+str(best[0])+"\") where itemid = '"+str(rec[0])+"'"
    a = c.execute(sql)
    db.commit()
 c.close()
 db.close()

def stampvat():
 db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
 c = db.cursor()
 for rec in uncoded:
  supplierid = str(rec[12])
  text = rec[9]
  splittext = text.split()
  splittextlength = len(text.split())
  bests = {}
  for wordpos,word in currencycandidates(rec).items():
   targetfeaturelength = len(word.split())-1
   for v in vrange:
    anchorpos = wordpos+v+((v>0)*targetfeaturelength)
    if anchorpos >= 0 and anchorpos < splittextlength:
     w = splittext[anchorpos]
     key = supplierid+' '+w+' '+str(v)+' 19'
     if key in dic:
      bests[word] = bests[word]+1 if word in bests else 1
  if len(bests)>0:
   best = sorted(bests,key=bests.get,reverse=True)
   if len(best)>0:
    sql = "update apdocs set otherjson=JSON_SET(otherjson,'$.f_19',\""+str(best[0])+"\") where itemid = '"+str(rec[0])+"'"
    a = c.execute(sql)
    db.commit()
 c.close()
 db.close()


def stampgoods():
 db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
 c = db.cursor()
 for rec in uncoded:
  supplierid = str(rec[12])
  text = rec[9]
  splittext = text.split()
  splittextlength = len(text.split())
  bests = {}
  for wordpos,word in currencycandidates(rec).items():
   targetfeaturelength = len(word.split())-1
   for v in vrange:
    anchorpos = wordpos+v+((v>0)*targetfeaturelength)
    if anchorpos >= 0 and anchorpos < splittextlength:
     w = splittext[anchorpos]
     key = supplierid+' '+w+' '+str(v)+' 18'
     if key in dic:
      bests[word] = bests[word]+1 if word in bests else 1
  if len(bests)>0:
   best = sorted(bests,key=bests.get,reverse=True)
   if len(best)>0:
    sql = "update apdocs set otherjson=JSON_SET(otherjson,'$.f_18',\""+str(best[0])+"\") where itemid = '"+str(rec[0])+"'"
    a = c.execute(sql)
    db.commit()
 c.close()
 db.close()


dic,lastrec = makefeatdic()

classcorpus = makeclasscorpus()
stampclass(classcorpus)
stampinvoiceno()
stamppo()
stamptaxdates()
stamptotals()
stampvat()
stampgoods()