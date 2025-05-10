from utils.load_and_split_docs import load_and_split_docs

from loaders.strong_csv_loader import load_strong_csv_docs
from splitters.strong_csv_splitter import split_strong_csv_docs
from extractors.strong_csv_extractor import strong_csv_extractor

csv_name = "strong"
csv = f".csv/{csv_name}.csv"

load_and_split_docs(
        None,                       #Arg: parent_id
        "StrongCSV",                #Arg: document_type
        load_strong_csv_docs,       #Arg: load_fn
        [csv],                      #Arg: load_params
        split_strong_csv_docs,      #Arg: split_fn
        strong_csv_extractor        #Arg: extract_fn
)