
import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Set;

import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

public class ExtractLinks
{
    public static void main(String[] args) throws IOException
    {        
        CreateMap cm = new CreateMap();
        HashMap<String,String> fileUrlMap,urlFileMap;
        fileUrlMap = cm.GetURLMap();
        urlFileMap = cm.GetFileURL();
        
        
        String dirPath = "NYD/NYD";
        File dir = new File(dirPath);
        Set<String> edges =  new HashSet<String>();
        
        for(File file :dir.listFiles())
        {
        	System.out.println("reading "+file.getName());
            if(fileUrlMap.get(file.getName()) !=null)
            {
                Document doc = Jsoup.parse(file,"UTF-8",fileUrlMap.get(file.getName()));
                Elements links = doc.select("a[href]");
                Elements media = doc.select("[src]");
                
                for(Element link : links)
                {
                    String url = link.attr("href").trim();
                    if(urlFileMap.containsKey(url))
                    {
                        edges.add(file.getName()+"   "+ urlFileMap.get(url));
                    }
                }
            }
        }
        
        FileWriter f1 = new FileWriter("edgeList.txt");
        for(String s:edges)
        {
        	System.out.println("Writing "+s);
            f1.append(s);
            f1.append("\n");
        }
        f1.close();
        
    }
    
  
}