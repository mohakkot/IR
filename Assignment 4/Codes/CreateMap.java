import java.io.BufferedReader;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.util.HashMap;

public class CreateMap 
{
    public HashMap<String,String> GetURLMap() throws IOException
    {
        
        BufferedReader br = null;
        String line = "";
        
        HashMap<String,String> fileurlMap = new HashMap<String,String>();
        
       try 
       {
            
            br = new BufferedReader(new FileReader("NYD/NYD Map.csv"));
            while ((line = br.readLine()) != null) 
            {
                String[] ln = line.split(",");
                String key = ln[0];
                String value = ln[1];

               fileurlMap.put(key, value);
                    
             }
            br.close();
       }
       catch (FileNotFoundException e) 
       {
            e.printStackTrace();
       }
       catch(IOException e){
    	   e.printStackTrace();
       }
	return fileurlMap;
    }
     
    public HashMap<String,String> GetFileURL() throws IOException
    {
    
        BufferedReader br = null;
        String line = "";
        
        HashMap<String,String> fileurlMap = new HashMap<String,String>();
     
       try 
       {
            
            br = new BufferedReader(new FileReader("NYD/NYD Map.csv"));
            while ((line = br.readLine()) != null) 
            {
                String[] ln = line.split(",");
                String key = ln[1];
                String value = ln[0];

               fileurlMap.put(key, value);
            }
       }
       catch (FileNotFoundException e) 
       {
            e.printStackTrace();
       }
       catch (IOException e) 
       {
            e.printStackTrace();
       } 
      br.close();
      return fileurlMap;
    }
}