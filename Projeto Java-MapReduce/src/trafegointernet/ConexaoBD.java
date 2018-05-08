package trafegointernet;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;

/**
 *
 * @author alberto
 */
public class ConexaoBD 
{    
    private static Connection conexao;

    public static boolean abrirConexaoPostgres(String servidor, String porta, String usuario, String senha, String banco) 
    {
        boolean conectou = true;

        if (getConexao() == null) 
        {
            try 
            {
                Class.forName("org.postgresql.Driver");
                String url = "jdbc:postgresql://" + servidor + ":" + porta + "/" + banco;
                setConexao(DriverManager.getConnection(url, usuario, senha));
                getConexao().setAutoCommit(false);
            } 
            catch (ClassNotFoundException | SQLException ex) 
            {
                conectou = false;
                System.out.println(ex.toString());
            }
        }
        
        return conectou;
    }

    public static boolean fecharConexaoPostgres() {
        boolean fechouConexao = false;
        try 
        {
            if (getConexao() != null) 
            {                
                getConexao().close();
                fechouConexao = getConexao().isClosed();
                setConexao(null);
            }
        } 
        catch (SQLException ex) 
        {
            System.out.println(ex.toString());
        }

        return fechouConexao;        
    }

    public static String consultarUmRegisto(String consulta, String nomeCampo) 
    {
        Statement stm;
        String valor = "";

        try 
        {
            if (getConexao() != null) 
            {
                stm = getConexao().createStatement();
                ResultSet rs = stm.executeQuery(consulta);

                while (rs.next()) 
                {
                    valor = rs.getString(nomeCampo);
                }
            }
        } 
        catch (SQLException ex) 
        {
            System.out.println(ex.toString());
        }

        return valor;
    }

    public static ResultSet consultarVariosRegistros(String consulta) 
    {            
        Statement stm;
        ResultSet rs = null;

        try 
        {
            if (getConexao() != null) 
            {
                stm = getConexao().createStatement();
                rs = stm.executeQuery(consulta);
            }
        } 
        catch (SQLException ex) 
        {
            System.out.println(ex.toString());
        }

        return rs;
    }

    public static int inserirAlterarRegistro(String comando) 
    {
        Statement stm;
        int resultado = 0;

        try 
        {            
            if (getConexao() != null) 
            {
                stm = getConexao().createStatement();
                resultado = stm.executeUpdate(comando);                
            }
        } 
        catch (SQLException ex) 
        {
            System.out.println(ex.toString());
        }

        return resultado;
    }

    public static Connection getConexao() 
    {
        return conexao;
    }

    private static void setConexao(Connection conexao) 
    {
        ConexaoBD.conexao = conexao;
    }
}