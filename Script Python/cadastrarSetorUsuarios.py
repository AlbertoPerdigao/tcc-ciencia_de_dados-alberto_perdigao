trafego_internet.py

	import psycopg2
	import psycopg2.extras
	import os
	import commands

	#PESQUISA USUARIO/SETOR NO SAMBA
	comando = "ldapsearch -h host -U integrador -w senha > /indicadores_internet/trafego_internet/ldapsearch.txt"
	os.system(comando)

	fileLdapsearch = file('/indicadores_internet/trafego_internet/ldapsearch.txt', 'r')

	dictLdapsearch = {}
	ultimoSetor = ""
	usuario = ""
	
	for row in fileLdapsearch.readlines():
	    if (len(row.replace("\n", "").strip()) == 0 and len(usuario) > 0 and len(ultimoSetor) > 0):
	        dictLdapsearch[usuario] = ultimoSetor
	        ultimoSetor = ""
	        usuario = ""
	    elif (row.find('sAMAccountName:') != -1):	        
	        if (len(row.split(" ")) == 2):
	            if (row.split(" ")[1].find('$') == -1):
	                usuario = row.split(" ")[1].replace("\n", "")	
	    elif (row.find('distinguishedName:') != -1):	        
	        if (row.find('DC=pge') != -1):
	            setores = row.replace("\n", "").split(",")
	            for setor in setores:
	                if (setor.find('OU=') != -1):
	                    ultimoSetor = setor.split("=")[1]
	
	fileLdapsearch.close

	#Conexao com o banco de dados
	conn = psycopg2.connect("dbname='nome_database' user='indicadores_internet' host='nome_ou_ip_do_host' password='senha' port='numero_porta'")	
	cur = conn.cursor(cursor_factory=psycopg2.extras.DictCursor)

	#Pesquisa os usuarios sem setor no banco de dados
	cur.execute("SELECT DISTINCT usuario FROM adm_indicadores_internet.trafego WHERE setor_id IS NULL;")
	rows = cur.fetchall()

	for row in rows:
	    usuario = row[0]
	    setor = dictLdapsearch.get(usuario)
	    if (setor is not None):	        
	        #Pesquisa o setor no banco de dados
	        cur.execute("SELECT id FROM adm_indicadores_internet.setor WHERE nome ilike '%"+ setor + "%' limit 1;")
	        rows = cur.fetchall()

	        if (len(rows) == 0):
	            print "Setor " + setor + " do usuario " + usuario + " nao encontrado na tabela adm_indicadores_internet.setor!"

	        for row in rows:
	            idSetor = row[0]
	            cur.execute("UPDATE adm_indicadores_internet.trafego SET setor_id = %s WHERE usuario = %s;", (idSetor, usuario))
	            conn.commit()

	conn.close()
