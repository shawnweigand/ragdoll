from langchain.document_loaders import GoogleDriveLoader
from langchain.text_splitter import RecursiveCharacterTextSplitter

folder_id="1wvLgaZsQJs0RUWTIUvZ3-xxQQVY5fIsc"

# Google Drive Loader
loader = GoogleDriveLoader(
    folder_id=folder_id,
    file_types=["document", "sheet"],
    recursive=False,
) 

docs = loader.load()

# text_splitter = RecursiveCharacterTextSplitter(
#     chunk_size=4000, chunk_overlap=0, separators=[" ", ",", "\n"]
# )
# texts = text_splitter.split_documents(docs)

print(docs)
