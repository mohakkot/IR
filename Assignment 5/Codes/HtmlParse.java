import java.io.File;
import java.io.FileInputStream;
import java.io.FileWriter;
import java.io.IOException;

import org.apache.tika.exception.TikaException;
import org.apache.tika.metadata.Metadata;
import org.apache.tika.parser.ParseContext;
import org.apache.tika.parser.html.HtmlParser;
import org.apache.tika.sax.BodyContentHandler;

import org.xml.sax.SAXException;

public class HtmlParse {

   public static void main(final String[] args) throws IOException,SAXException, TikaException {

      //detecting the file type
	  String dirPath = "NYD/NYD";
      File dir = new File(dirPath);
      FileWriter f1 = new FileWriter("big.txt");
      int count = 1;
      int i = 1;
      for(File file :dir.listFiles())
      {		
    	  if (count == 1000){
    		  System.out.println(i++);
	    	  count = 0;
	    	
	      }
    	  BodyContentHandler handler = new BodyContentHandler();
	      Metadata metadata = new Metadata();
	      FileInputStream inputstream = new FileInputStream(new File("NYD/NYD/"+file.getName()));
	      count++;
	      ParseContext pcontext = new ParseContext();
      
	      //Html parser 
	      HtmlParser htmlparser = new HtmlParser();
	      htmlparser.parse(inputstream, handler, metadata,pcontext);
	      //System.out.println("Contents of the document:" + handler.toString());
	      //System.out.println("Metadata of the document:");
	      String[] metadataNames = metadata.names();
	      f1.append(handler.toString());
	      f1.append("\n");
      
	      /*for(String name : metadataNames) {
	    	  f1.append(name + ":   " + metadata.get(name));  
	      }*/
      }
   }
}