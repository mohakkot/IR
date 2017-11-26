import re
name = "big.txt"
handle = open(name, 'r')
writer = open("final.txt", "w")
lines= handle.readlines();
for line in lines:
    line = line.strip("\n\r")
    words = line.strip('\r\n').lstrip("\t").split(" ")
    for word in words:
        if word != "":
            word = word.strip()
            word = re.sub("""['’`“”,:"&;?<>~$!)(*#@]""","",word)
            #print(word)
            writer.writelines(word+"\n")