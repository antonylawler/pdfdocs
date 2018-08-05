import json
import pymysql
import re
from sklearn import tree
clf             = tree.DecisionTreeClassifier()

db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
c = db.cursor()

e = c.execute("select itemid,textfromfile from apdocs where pageto>pagefrom and pageto is not null")
dontsplit = c.fetchall()

e = c.execute("SELECT a.itemid,b.itemid,a.textfromfile,b.textfromfile FROM apdocs a, apdocs b WHERE a.emailuid = b.emailuid AND b.itemid > a.itemid and a.doctype != 'NOCLASS' AND b.doctype != 'NOCLASS'")
dosplit = c.fetchall()

e = c.execute("select itemid,textfromfile,pages,filename from apdocs where pages > 1 and pageto is null")
check = c.fetchall()

e = c.execute("select itemid,supplierid,doctype,textfromfile from apdocs where supplierid is not null and supplierid != '' ")
precoded = c.fetchall()

e = c.execute("select itemid,otherjson,textfromfile from apdocs where supplierid is null or supplierid = ''")
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


def guessclass(wordset,dicindex):
 bestarray = {'0':0,'1':0,'2':0,'3':0,'4':0,'5':0,'6':0,'7':0,'8':0,'9':0}
 for word in wordset:
  if word in dicindex:
   for clas in dicindex[word]:
    bestarray[clas] = 1 if clas not in bestarray else bestarray[clas] + 1
 best = max(bestarray, key=bestarray.get)
 s    = sorted(bestarray.values())[-5:]
 return best,s

splitcorpus = {}

for rec in dontsplit:
 for p in range(0,len(rec[1].split("\f"))-1):
  s = makepagebigrams(rec[1].split("\f")[p]+"\f"+rec[1].split("\f")[p+1])
  for w in s:
   splitcorpus.setdefault(w,set()).add('dontsplit')

classcorpus = {}
allclasses = set()
for rec in precoded:
 wordset = makebigrams(rec[3])
 for word in wordset:
  classcorpus.setdefault(word,set()).add(rec[1]+'_'+rec[2])

 
for rec in dosplit:
 pages = rec[2].split("\f")[-1]+"\f"+rec[3].split("\f")[0]
 s = makepagebigrams(pages)
 for w in s:
  splitcorpus.setdefault(w,set()).add('split')

db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
c = db.cursor()
for rec in check:
 ps = []
 for p in range(0,len(rec[1].split("\f"))-1):
  s = makepagebigrams(rec[1].split("\f")[p]+"\f"+rec[1].split("\f")[p+1])
  ans,li = guessclass(s,splitcorpus)
  if ans == 'split':
   ps.append(p+1)
 sql = "update apdocs set otherjson = '{\"ps\":"+str(ps)+"}' where itemid = "+str(rec[0])
 a = c.execute(sql)
 db.commit()

for rec in uncoded:
 wordset = rec[2]
 best,bestarray = guessclass(wordset,classcorpus)
 if rec[1] is None:
  sql = "update apdocs set otherjson = json_set('{}','$.supplierid','"+best+"') where itemid = "+str(rec[0])
 else:
  sql = "update apdocs set otherjson = json_set(otherjson,'$.supplierid','"+best+"') where itemid = "+str(rec[0])
 c.execute(sql)
db.commit()

c.close()
db.close()
