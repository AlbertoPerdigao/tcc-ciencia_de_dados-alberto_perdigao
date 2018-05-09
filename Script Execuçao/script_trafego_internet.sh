#!/bin/bash

qtd_processos=`ps aux | grep -c "script_trafego_internet.sh"`

if [ "$qtd_processos" -lt 4 ]
then

	#Limpa arquivos processados
	rm /indicadores_internet/trafego_internet/access_processado.log 2>/tmp/erro

	#Envia arquivo access.log pro Hadoop
	/usr/local/hadoop/bin/hadoop fs -put /var/log/squid3/access.log /indicadores_internet/trafego_internet/access_processado.log

	#Executa o .jar no Hadoop e guarda os resultados
	/usr/local/hadoop/bin/hadoop jar /indicadores_internet/trafego_internet/TrafegoInternet.jar /indicadores_internet/trafego_internet/access_processado.log /indicadores_internet/trafego_internet

	#Executa script python pra cadastrar o setor aos usuarios no banco Postgres
	python /indicadores_internet/trafego_internet/cadastrarSetorUsuarios.py

fi
