import json
import pymysql
import re
#from sklearn import tree
#clf             = tree.DecisionTreeClassifier()
rx = []
rx.append("((Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]{0,6}(-|\\/| |,){1,2}(0|1|2|3)?[0-9](st|nd|rd|th)?(-|\\/| |,){1,2}(19|20)?\\d ?\\d)")
rx.append("((0|1|2|3)?\\d(st|nd|rd|th)?(-|\\/| |,){1,2}(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]{0,6}(st|nd|rd|th)?(-|\\/| |,){1,2}(19|20)?\\d ?\\d)")
rx.append("((0|1|2|3)?\d(-|\\/|,|\\.){1,2} ?(0|1|2)? ?\d(-|\\/|,|\\.){1,2} ?(19|20)?\\d ?\\d)")

db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
c = db.cursor()

e = c.execute("select * from apdocs where posted is not null order by editdate")
precoded = c.fetchall()

e = c.execute("select * from apdocs where doctype in ('I','C') and posted is null")
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
   classcorpus.setdefault(word,set()).add(rec[12]+'_'+rec[13])

def stampclass():
 db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
 c = db.cursor()
 for rec in uncoded:
  wordset = makebigrams(rec[9])
  best,bestarray = guessclass(wordset,classcorpus)
  if max(bestarray)>20:
   supplierid,doctype = best.split('_')
   sql = "update apdocs set supplierid = '"+supplierid+"', doctype='"+doctype+"' where itemid = '"+str(rec[0])+"'"
   a = c.execute(sql)
   db.commit()
 c.close()
 db.close()

vrange = (-5,-4,-3,-2,-1,1,2,3,4)
dic = {}
gendic = {}
excludes = ['2016','2017','2018','2019','SP2','07152']

def makefeatdic():
 for rec in precoded:
  if rec[13] == 'C' or rec[13] == 'I':
   invoiceno = rec[15]
   textfromfile = rec[9].split()
   supplierid = rec[12]
   porderno = rec[16]
   taxdate = json.loads(rec[23])['f_17'][0]
   for dpos,wordval in enumerate(textfromfile):
    if wordval == invoiceno:
     for i in vrange:
      if (i+dpos) < 0 or i+dpos>=len(textfromfile):
       continue
      key = supplierid+'_'+textfromfile[dpos+i]+'_'+str(i)+'_INV'
      dic[key] = dic[key]+1 if key in dic else 1
      key = textfromfile[dpos+i]+'_'+str(i)+'_INV'
      gendic[key] = gendic[key]+1 if key in gendic else 1
    if wordval == porderno:
     for i in vrange:
      if (i+dpos) < 0 or i+dpos>=len(textfromfile):
       continue
      key = supplierid+'_'+textfromfile[dpos+i]+'_'+str(i)+'_PO'
      dic[key] = dic[key]+1 if key in dic else 1
      key = textfromfile[dpos+i]+'_'+str(i)+'_PO'
      gendic[key] = gendic[key]+1 if key in gendic else 1
   if taxdate:
    taxwords = len(taxdate.split())
    tx = rec[9]
    txs = tx.split()
    p = tx.find(taxdate,0)
    while p >= 0:
     pr = len(tx[:p].split())
     for i in vrange:
      if pr+i<0 or pr+i+taxwords-1>=len(txs):
       continue
      key = supplierid+'_'+txs[pr+i+(taxwords-1)*(i>0)]+'_'+str(i)+'_TAXDATE'
      dic[key] = dic[key]+1 if key in dic else 1
      key = txs[pr+i+(taxwords-1)*(i>0)]+'_'+str(i)+'_TAXDATE'
      gendic[key] = gendic[key]+1 if key in gendic else 1
     p = rec[9].find(taxdate,p+1)

   

def stampinvoiceno():
 db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
 c = db.cursor()
 for rec in uncoded:
  best = {}
  genbest = {}
  textfromfile = rec[9].split()
  supplierid = str(rec[12])
  for dpos,wordval in enumerate(textfromfile):
   if not(re.match('.*\\d+.*',wordval)):
    continue
   if re.match('\\d+\\.\\d\\d',wordval):
    continue
   if wordval in excludes:
    continue
   if len(wordval)<3:
    continue
   if re.match('\\d{1,2}\\/\\d\\d\\/\\d\\d',wordval):
    continue
   keylist = set()
   genkeylist = set()
   for i in vrange:
    if (i+dpos) < 0 or i+dpos>=len(textfromfile):
     continue
    keylist.add(supplierid+'_'+textfromfile[dpos+i]+'_'+str(i))
    genkeylist.add(textfromfile[dpos+i]+'_'+str(i))
   l = setdict.intersection([x+'_PO' for x in keylist])
   v = 0
   if len(l)>0:
    for k in l:
     v += dic[k]
     best[dpos] = v
   l = gensetdict.intersection([x+'_INV' for x in genkeylist])
   v = 0
   if len(l)>0:
    for k in l:
     v += gendic[k]
     genbest[dpos] = v
  best = sorted(best,key=best.get,reverse=True)
  genbest = sorted(genbest,key=genbest.get,reverse=True)
  bestguess = ''
  if len(best)>0:
   bestguess = textfromfile[best[0]];
  elif len(genbest)>0:
   bestguess = textfromfile[genbest[0]];
  if bestguess != '':
   sql = "update apdocs set invoiceno = '"+bestguess+"' where itemid = '"+str(rec[0])+"'"
   a = c.execute(sql)
   db.commit()
 c.close()
 db.close()

def stamppo():
 db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
 c = db.cursor()
 for rec in uncoded:
  best = {}
  genbest = {}
  textfromfile = rec[9].split()
  supplierid = str(rec[12])
  for dpos,wordval in enumerate(textfromfile):
   if wordval in excludes:
    continue
   if len(wordval)<3:
    continue
   if not(re.match('.*\\d',wordval)):
    continue
   if not(re.match('.*P.*',wordval)):
    continue
   keylist = set()
   genkeylist = set()
   for i in vrange:
    if (i+dpos) < 0 or i+dpos>=len(textfromfile):
     continue
    keylist.add(supplierid+'_'+textfromfile[dpos+i]+'_'+str(i))
    genkeylist.add(textfromfile[dpos+i]+'_'+str(i))
   l = setdict.intersection([x+'_PO' for x in keylist])
   v = 0
   if len(l)>0:
    for k in l:
     v += dic[k]
     best[dpos] = v
   l = gensetdict.intersection([x+'_PO' for x in genkeylist])
   v = 0
   if len(l)>0:
    for k in l:
     v += gendic[k]
     genbest[dpos] = v
  best = sorted(best,key=best.get,reverse=True)
  genbest = sorted(genbest,key=genbest.get,reverse=True)
  bestguess = ''
  if len(best)>0:
   bestguess = textfromfile[best[0]];
  elif len(genbest)>0:
   bestguess = textfromfile[genbest[0]];
  if bestguess == '':
   sr = re.search('\\d[^0-9]{1,3}(P|p)(O|0|o) ?\\d{1,5}',rec[9])
   if sr:
    bestguess = sr[0]
   else:
    bestguess = ''
  if bestguess != '':
   sql = "update apdocs set purchaseorder = '"+bestguess+"' where itemid = '"+str(rec[0])+"'"
   a = c.execute(sql)
   db.commit()
  else:
   print("Fail ",rec[6])

 c.close()
 db.close()

makefeatdic()
setdict = set(dic)
gensetdict = set(gendic)
#stampinvoiceno()
stamppo()
stamptaxdate()

for rec in precoded:
 textfromfile = rec[9]