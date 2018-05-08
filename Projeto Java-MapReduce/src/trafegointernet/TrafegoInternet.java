package trafegointernet;

import java.io.IOException;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.HashMap;
import java.util.Map;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.FileSystem;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.io.LongWritable;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;
import org.apache.hadoop.util.GenericOptionsParser;

/**
 *
 * @author alberto
 */
public class TrafegoInternet
{    
    static long TEMPO_ULTIMO_REGISTRO_MAPEADO;
    static String TEMPO_ULTIMO_REGISTRO_LIDO;    
    static Map<String,String> FONTES = new HashMap<>();
    static String ID_CUSTO_TRAFEGO = "";
    static String ID_FONTE_INTERNET = "";
    static String ID_FONTE_INTERNET_ECONOMIA = "";
    static String ID_FONTE_OUTRAS_FONTES = "";
    static boolean E_FONTE_INTERNET;    
    static final String SERVIDOR = "servidor";
    static final String BANCO = "banco";
    static final String PORTA = "porta";
    static final String USUARIO = "indicadores_internet";
    static final String SENHA = "indicadores_internet";
    
    public static void main(String[] args) throws IOException, InterruptedException, ClassNotFoundException, SQLException 
    {   
        if (ConexaoBD.abrirConexaoPostgres(SERVIDOR, PORTA, USUARIO, SENHA, BANCO))
        {                           
            //Consulta o custo por gigabyte trafegado mais atual
            String consulta = "SELECT id FROM adm_indicadores_internet.custo_trafego WHERE data_inicio <= now() ORDER BY data_inicio DESC LIMIT 1;";
            ID_CUSTO_TRAFEGO = ConexaoBD.consultarUmRegisto(consulta, "id");
            
            consulta = "SELECT id, nome FROM adm_indicadores_internet.fonte ORDER BY id;";
            try (ResultSet rs = ConexaoBD.consultarVariosRegistros(consulta)) {
                while(rs.next())
                    FONTES.put(rs.getString("id"), rs.getString("nome"));
            }
            
            //Se não encontrar um custo ou uma fonte, termina o sistema
            if (ID_CUSTO_TRAFEGO.isEmpty() || FONTES.isEmpty())            
                System.out.println("Não foi possível encontrar o custo do tráfego de internet ou uma fonte para consulta!\n"
                        + "Verifique a tabela adm_indicadores_internet.custo_trafego ou a tabela adm_indicadores_internet.fonte");
            else
            {                
                for (String id : FONTES.keySet()) 
                {                     
                    //Captura o id das fontes Internet, Internet Economia e Outras Fontes
                    String fonte = FONTES.get(id);
                    
                    switch (fonte.toLowerCase()) 
                    {
                        case "internet":
                            ID_FONTE_INTERNET = id;
                            break;
                        case "internet economia":
                            ID_FONTE_INTERNET_ECONOMIA = id; 
                            break;
                        case "outras fontes":
                            ID_FONTE_OUTRAS_FONTES = id;
                            break;
                        default:
                            break;
                    }
                }
                                
                //Se tem as fontes Internet, Internet Economia e Outras Fontes
                if (!ID_FONTE_INTERNET.isEmpty() && !ID_FONTE_INTERNET_ECONOMIA.isEmpty() && !ID_FONTE_OUTRAS_FONTES.isEmpty())
                {
                    FONTES.remove(ID_FONTE_INTERNET);
                    FONTES.remove(ID_FONTE_INTERNET_ECONOMIA);
                    FONTES.remove(ID_FONTE_OUTRAS_FONTES);
                    
                    executarJob(args, true, "Internet", true, ID_FONTE_INTERNET);
                    executarJob(args, true, "Economia", false, ID_FONTE_INTERNET_ECONOMIA);
                    
                    //Se exitirem outras fontes (ex. Facebook, Youtube)...
                    if (FONTES.size() > 0)                                            
                        executarJob(args, false, "Outras_Fontes", true, ID_FONTE_OUTRAS_FONTES);                                        
                }
                else
                    System.out.println("Não foi possível encontrar as fontes Internet, Internet - Economia e Outras Fontes!\n"
                            + "Verifique a tabela adm_indicadores_internet.fonte e adicione essas 3 fontes.");
            }
                        
            ConexaoBD.fecharConexaoPostgres();
            System.exit(0);            
        }
        else
        {
            System.out.println("Não foi possível se conectar ao banco de dados!");
            System.exit(1);
        }
    }
    
    /*
     * args: argumentos da aplicação
     * eFonteInternet: verifica se a Fonte é Internet, caso contrário assume como Outras Fontes
     * nomeTrafego: nome para definir o tráfego
     * gerarTrafegoInternet: verifica se é para gerar tráfego de Internet, caso contrário gera Economia
     * idFonte: id da Fonte para consulta e inserção do tempo do ultimo registro mapeado
     */
    public static void executarJob(String[] args, boolean eFonteInternet, String nomeTrafego, boolean gerarTrafegoInternet, String idFonte) 
            throws IOException, InterruptedException, ClassNotFoundException, SQLException
    {
        E_FONTE_INTERNET = eFonteInternet;
          
        String consulta = String.format("SELECT tempo FROM adm_indicadores_internet.ultimo_registro_mapeado WHERE fonte_id = %s ORDER BY ID DESC LIMIT 1;",
                idFonte);

        String tempo = ConexaoBD.consultarUmRegisto(consulta, "tempo");                
        TEMPO_ULTIMO_REGISTRO_MAPEADO = tempo.isEmpty() ? 0 : Long.parseLong(tempo.substring(0, tempo.indexOf(".")));

        Configuration conf = new Configuration();
        FileSystem fs = FileSystem.get(conf);
        String[] files = new GenericOptionsParser(conf, args).getRemainingArgs();
        Path input = new Path(files[0]);
        
        String jobName = "Trafego " + nomeTrafego;
        Job job = new Job(conf, jobName);
        job.setJarByClass(TrafegoInternet.class);
        
        if (gerarTrafegoInternet)
        {
            job.setMapperClass(TrafegoInternet.MapTrafegoInternet.class);        
            job.setReducerClass(TrafegoInternet.ReduceTrafegoInternet.class);
        }
        else //Economia
        {
            job.setMapperClass(MapTrafegoInternetEconomia.class);        
            job.setReducerClass(ReduceTrafegoInternetEconomia.class);
        }
        //job.setMapOutputKeyClass(Text.class);
        //job.setMapOutputValueClass(LongWritable.class);
        job.setOutputKeyClass(Text.class);
        job.setOutputValueClass(LongWritable.class);

        //Verifica a existência do diretório de Resultados. Se existir, deleta o diretório.
        //O Hadoop retorna erro caso o diretório exista.
        String caminhoResultado = files[1] + "/Resultados/Resultado_" + nomeTrafego;
        Path output = new Path(caminhoResultado);
        if (fs.exists(output))
            fs.delete(output, true);

        FileInputFormat.addInputPath(job, input);
        FileOutputFormat.setOutputPath(job, output);

        //Insere o último registro lido do tráfego de internet (arquivo access.log) no banco 
        //após a execução do job.
        if (job.waitForCompletion(true))
        {                    
            String comando = String.format("INSERT INTO adm_indicadores_internet.ultimo_registro_mapeado (tempo, fonte_id, created_at, updated_at) VALUES ('%s', %s, now(), now());", 
                    TEMPO_ULTIMO_REGISTRO_LIDO, idFonte);
            ConexaoBD.inserirAlterarRegistro(comando);
            ConexaoBD.getConexao().commit();
        }
    }
        
    //Classe Map para Trafego de Internet
    public static class MapTrafegoInternet extends Mapper<LongWritable, Text, Text, LongWritable>
    {               
        Text outputKey = new Text();
        LongWritable outputValue = new LongWritable();
                
        @Override
        public void map(LongWritable key, Text value, Context context) throws IOException, InterruptedException
        {
            //A linha é formada pelos seguintes atributos:
            //tempo  duração  endereço_cliente  codigo_resultante  bytes  método_requisição  url  usuário  código_hierarquia  tipo
            //Ex.: 1516892461.126    502 192.168.?.? TCP_MISS/200 42041 GET http://portal.estacio.br/ alberto HIER_DIRECT/177.184.128.208 text/html
            
            String linha = value.toString();
            linha = linha.replaceAll("\\s+", " ");
            String[] palavras = linha.split(" ");
                        
            long tempoRegistro = Long.parseLong(palavras[0].substring(0, palavras[0].indexOf(".")));                        
            long bytes = Long.parseLong(palavras[4]);            
            
            if (palavras.length == 10 && tempoRegistro > TEMPO_ULTIMO_REGISTRO_MAPEADO 
                    && (!palavras[3].contains("TCP_DENIED")) && bytes > 0 
                    && (!palavras[6].contains("drive.pge.ce.gov.br")))
            {                                
                String data = new SimpleDateFormat("yyyy-MM-dd").format(new Date(tempoRegistro * 1000L));
                String usuario = palavras[7];
                
                if (E_FONTE_INTERNET)
                {                      
                    String dataUsuarioFonte = data + " " + usuario + " " + ID_FONTE_INTERNET;
                    
                    outputKey.set(dataUsuarioFonte);
                    outputValue.set(bytes);
                    context.write(outputKey, outputValue);
                }
                else //Otras Fontes (Facebook, Youtube, Instagram...)
                {        
                    //String idFonte = FONTES.entrySet().stream().filter(e -> e.getValue().equals(nomeFonte)).findFirst().map(Map.Entry::getKey).orElse(null);
                    for (String idFonte : FONTES.keySet()) 
                    {                        
                        String nomeFonte = FONTES.get(idFonte);                        
                        String dataUsuarioFonte = data + " " + usuario + " " + idFonte;
                        boolean escreverResultado = false;
                                                                        
                        switch (nomeFonte.toLowerCase())
                        {                            
                            case "facebook":
                                if (palavras[6].contains("facebook.com") || palavras[6].contains("fbcdn.net") ||
                                        palavras[6].contains("akamaihd.net"))
                                    escreverResultado = true;
                                break;
                            case "youtube":
                                if (palavras[6].contains("youtube.com") || palavras[6].contains("googlevideo.com"))
                                    escreverResultado = true;
                                break;
                            case "instagram":
                                if (palavras[6].contains("instagram.com"))
                                    escreverResultado = true;
                                break;
                            default:
                                if (palavras[6].contains(nomeFonte.toLowerCase()))
                                    escreverResultado = true;
                                break;
                        }

                        if (escreverResultado)
                        {                    
                            outputKey.set(dataUsuarioFonte);
                            outputValue.set(bytes);
                            context.write(outputKey, outputValue);
                            break;
                        }
                    }                    
                }
            }
            
            TEMPO_ULTIMO_REGISTRO_LIDO = palavras[0];            
        }
    }
           
    //Classe Reduce do Trafego de Internet
    public static class ReduceTrafegoInternet extends Reducer<Text, LongWritable, Text, LongWritable>
    {
        private final LongWritable result = new LongWritable();
        
        @Override
        public void reduce(Text key, Iterable<LongWritable> values, Context context) throws IOException, InterruptedException
        {                        
            long somaBytes = 0;
            for(LongWritable valor : values)            
                somaBytes += valor.get();            
                                   
            //O parâmetro "key" é equivalente a variável dataUsuarioFonte da classe MapTrafegoInternet (data + " " + usuario + " " + idFonte);
            String[] dataUsuarioFonte = key.toString().split(" ");            
                                    
            String consulta = String.format("SELECT 1 as existe_usuario FROM adm_indicadores_internet.trafego WHERE periodo = '%s' AND usuario = '%s' AND fonte_id = %s AND custo_trafego_id = %s;", 
                    dataUsuarioFonte[0], dataUsuarioFonte[1], dataUsuarioFonte[2], ID_CUSTO_TRAFEGO);
            String comando;
            
            //Verifica se existe o tráfego (usuario/periodo/fonte_id/custo_trafego_id) cadastrado. 
            //Se já existe, soma os bytes, senão insere o trafego.
            if (ConexaoBD.consultarUmRegisto(consulta, "existe_usuario").equals("1"))            
                comando = String.format("UPDATE adm_indicadores_internet.trafego SET bytes = bytes + %s, updated_at = now() WHERE periodo = '%s' AND usuario = '%s' AND fonte_id = %s AND custo_trafego_id = %s;", 
                        somaBytes, dataUsuarioFonte[0], dataUsuarioFonte[1], dataUsuarioFonte[2], ID_CUSTO_TRAFEGO);
            else            
                comando = String.format("INSERT INTO adm_indicadores_internet.trafego (periodo, usuario, fonte_id, bytes, custo_trafego_id, created_at, updated_at) VALUES ('%s', '%s', %s, %s, %s, now(), now());", 
                        dataUsuarioFonte[0], dataUsuarioFonte[1], dataUsuarioFonte[2], somaBytes, ID_CUSTO_TRAFEGO);            
            
            ConexaoBD.inserirAlterarRegistro(comando);
                        
            result.set(somaBytes);
            context.write(key, result);
        }
    }
    
    //Classe Map para Trafego de Internet Economia
    public static class MapTrafegoInternetEconomia extends Mapper<LongWritable, Text, Text, LongWritable>
    {               
        Text outputKey = new Text();
        LongWritable outputValue = new LongWritable();
                
        @Override
        public void map(LongWritable key, Text value, Context context) throws IOException, InterruptedException
        {
            //A linha é formada pelos seguintes atributos:
            //tempo  duração  endereço_cliente  codigo_resultante  bytes  método_requisição  url  usuário  código_hierarquia  tipo
            //Ex.: 1516892461.126    502 192.168.?.? TCP_MISS/200 42041 GET http://portal.estacio.br/ alberto HIER_DIRECT/177.184.128.208 text/html
            
            String linha = value.toString();
            linha = linha.replaceAll("\\s+", " ");
            String[] palavras = linha.split(" ");
                        
            long tempoRegistro = Long.parseLong(palavras[0].substring(0, palavras[0].indexOf(".")));                        
            long bytes = Long.parseLong(palavras[4]);            
            
            if (palavras.length == 10 && tempoRegistro > TEMPO_ULTIMO_REGISTRO_MAPEADO 
                    && bytes > 0 && (!palavras[6].contains("drive.pge.ce.gov.br"))
                    && (palavras[3].contains("TCP_DENIED") || palavras[3].contains("TCP_HIT")))
            {                                
                String data = new SimpleDateFormat("yyyy-MM-dd").format(new Date(tempoRegistro * 1000L));
                String dataTipo = "";

                if (palavras[3].contains("TCP_DENIED"))
                    dataTipo = data + " acesso_bloqueado";
                else if (palavras[3].contains("TCP_HIT"))
                    dataTipo = data + " acesso_cache";
                
                outputKey.set(dataTipo);
                outputValue.set(bytes);
                context.write(outputKey, outputValue);
            }
            
            TEMPO_ULTIMO_REGISTRO_LIDO = palavras[0];            
        }
    }
    
    //Classe Reduce do Trafego de Internet
    public static class ReduceTrafegoInternetEconomia extends Reducer<Text, LongWritable, Text, LongWritable>
    {
        private final LongWritable result = new LongWritable();
        
        @Override
        public void reduce(Text key, Iterable<LongWritable> values, Context context) throws IOException, InterruptedException
        {                        
            long somaBytes = 0;
            for(LongWritable valor : values)            
                somaBytes += valor.get();            
                                   
            //O parâmetro "key" é equivalente a variável dataTipo da classe MapTrafegoInternetEconomia (data + "acesso_bloqueado" ou data + "acesso_cache");
            String[] dataTipo = key.toString().split(" ");            
                                    
            String consulta = String.format("SELECT 1 as existe_tipo FROM adm_indicadores_internet.economia WHERE periodo = '%s' AND tipo = '%s' AND custo_trafego_id = %s;", 
                    dataTipo[0], dataTipo[1], ID_CUSTO_TRAFEGO);
            String comando;
            
            //Verifica se existe o tráfego de economia (periodo/tipo/custo_trafego_id) cadastrado. 
            //Se já existe, soma os bytes, senão insere o trafego de economia.
            if (ConexaoBD.consultarUmRegisto(consulta, "existe_tipo").equals("1"))            
                comando = String.format("UPDATE adm_indicadores_internet.economia SET bytes = bytes + %s, updated_at = now() WHERE periodo = '%s' AND tipo = '%s' AND custo_trafego_id = %s;", 
                        somaBytes, dataTipo[0], dataTipo[1], ID_CUSTO_TRAFEGO);            
            else            
                comando = String.format("INSERT INTO adm_indicadores_internet.economia (periodo, tipo, bytes, custo_trafego_id, created_at, updated_at) VALUES ('%s', '%s', %s, %s, now(), now());", 
                        dataTipo[0], dataTipo[1], somaBytes, ID_CUSTO_TRAFEGO);
                                   
            ConexaoBD.inserirAlterarRegistro(comando);
                        
            result.set(somaBytes);
            context.write(key, result);
        }
    }
}