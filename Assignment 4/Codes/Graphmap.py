from decimal import *

getcontext().prec = 6
import networkx as nx


def createGraph():
    #dir = 'C:\Users\mohak\workspace\Extracting Links\NYD\NYD\'
    G = nx.read_edgelist("edgeList.txt", create_using=nx.DiGraph())
    pr = nx.pagerank(G, alpha=0.85, personalization=None, max_iter=30, tol=1e-06, nstart=None, weight='weight',
                     dangling=None)
    fh = open("external_pageRankFile.txt", "w")
    fh.truncate()

    for k, v in pr.items():
        fh.write(str(k) + "=" + str(v) + "\n")
    fh.close()


if __name__ == '__main__':
    createGraph()