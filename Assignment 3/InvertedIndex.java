import java.io.IOException;
//import java.util.StringTokenizer;
import java.util.*;
import java.util.concurrent.ConcurrentHashMap;

import org.apache.hadoop.fs.Path;
import org.apache.*;
import org.apache.hadoop.io.IntWritable;
import org.apache.hadoop.io.LongWritable;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;

public class InvertedIndex {

  public static class TokenizerMapper
       extends Mapper<LongWritable, Text, Text, Text>{
	  private Text word = new Text();
	  private Text docid = new Text();
	  public void map(LongWritable key, Text value, Context context
                    ) throws IOException, InterruptedException {
      //StringTokenizer itr = new StringTokenizer(value.toString());
      String[] line = value.toString().split("\t",2);
	  docid.set(line[0]);
	  String textstr = line[1];
	  String[] words = textstr.split("\\s+");
	  int i =0;
      while (i < words.length) {
         word.set(words[i]);
        context.write(word, docid);
        i++;
      }
    }
  }

  public static class IntSumReducer
       extends Reducer<Text,Text,Text,Text> {
	
    public void reduce(Text key, Iterable<Text> values,
                       Context context) throws IOException, InterruptedException {
	  //boolean check = true;
	  int sum;
	  ConcurrentHashMap<String, Integer> ind = new  ConcurrentHashMap<>();
      for (Text v : values) {
        String s = new String(v.toString());
		if(!s.isEmpty()){
			if(!ind.containsKey(s) || ind.isEmpty() || ind.get(s)==null){
			ind.put(s,1);
			}
			else{
				sum = ind.get(s);
				int cnt = sum + 1;
				ind.put(s, cnt);
			}
		}
      }
		
      /*Iterator iter = ind.entrySet().iterator();
      StringBuilder sb = new StringBuilder();
      if(iter.hasNext()){
    	  sb.append(iter.next());
    	  while(iter.hasNext()) {
    		  Map.Entry pair = (Map.Entry)iter.next();
    		  sb.append(pair.getKey() + ":" + pair.getValue() + " ");
    	  }
      }
      String sbf = new String(sb);*/
      String[] ky = new String[ind.size()];
      Integer[] val = new Integer[ind.size()];
      int k = 0;
      for (Map.Entry<String, Integer> mapEntry : ind.entrySet()) {
          ky[k] = mapEntry.getKey();
          val[k] = mapEntry.getValue();
          k++;
      }
      StringBuilder strBuilder = new StringBuilder(); 
      for (int j =0; j<ky.length;j++){
    	  strBuilder.append(ky[j] + ":" + val[j] + " ");
      }
      String newString = strBuilder.toString();
      Text documentList = new Text();
      documentList.set(newString);
      context.write(key, documentList);
    }
  }

  public static void main(String[] args) throws Exception {
    Job job = new Job();
    job.setJarByClass(InvertedIndex.class);
    job.setMapperClass(TokenizerMapper.class);
    job.setCombinerClass(IntSumReducer.class);
    job.setReducerClass(IntSumReducer.class);
    job.setOutputKeyClass(Text.class);
    job.setOutputValueClass(Text.class);
    
    FileInputFormat.addInputPath(job, new Path(args[0]));
    FileOutputFormat.setOutputPath(job, new Path(args[1]));
    job.waitForCompletion(true);
    System.exit(job.waitForCompletion(true) ? 0 : 1);
  }
}