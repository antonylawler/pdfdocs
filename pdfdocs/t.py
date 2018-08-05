import pymysql
db = pymysql.connect(host='127.0.0.1',user='root',password='password',db='docs')
c = db.cursor()
sql = "update apdocs set otherjson = 'x';"
a = c.execute(sql)
c.execute('commit')
print(a)
c.close()

db.close()
