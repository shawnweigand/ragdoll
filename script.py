from loaders.drive_loader import load_drive_folder_docs
from splitters.recursive_splitter import split_recursive_docs

docs = load_drive_folder_docs(folder_id="1wvLgaZsQJs0RUWTIUvZ3-xxQQVY5fIsc")
texts = split_recursive_docs(docs)
print(texts[1])