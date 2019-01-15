#!/bin/bash

script_rodando=`cat /etc/scripts/indicadores_internet/script_rodando.txt`

if [ "$script_rodando" -eq 0 ]
then
        echo "1" > /etc/scripts/indicadores_internet/script_rodando.txt

        #Limpa arquivos processados
        rm /etc/scripts/indicadores_internet/trafego_internet/access_processado.log 2>/tmp/erro

        #Envia o arquivo access.log pro Hadoop
        /usr/local/hadoop/bin/hadoop fs -put /mnt/logsquid/access.log /etc/scripts/indicadores_internet/trafego_internet/access_processado.log

        #Executa o .jar no Hadoop e guarda os resultados
        /usr/local/hadoop/bin/hadoop jar /etc/scripts/indicadores_internet/trafego_internet/TrafegoInternet.jar /etc/scripts/indicadores_internet/trafego_internet/access_processado.log /etc/scripts/indicadores_internet/trafego_internet

        #Executa script python pra cadastrar o setor aos usuarios no banco Postgres
        python /etc/scripts/indicadores_internet/trafego_internet/cadastrarSetorUsuarios.py

        echo "0" > /etc/scripts/indicadores_internet/script_rodando.txt
fi

