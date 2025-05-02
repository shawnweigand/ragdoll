from services.RagdollService import RagdollService

def load_and_split_docs(
        document_id,
        docuemnt_type,
        load_fn, 
        load_params, 
        split_fn
    ):
    """
    Load and split documents using the provided functions and parameters.
    
    Args:
        load_fn (callable): Function to load documents.
        load_params (dict): Parameters for the loading function.
        split_fn (callable): Function to split documents.
    """

    try:
        # Load documents
        docs = load_fn(*load_params)
    except Exception as e:
        with (open("error.log", "a")) as f:
            f.write(f"Error loading documents: {e}\n")
        return
    
    try:
        # Split documents
        texts = split_fn(docs)
    except Exception as e:
        with (open("error.log", "a")) as f:
            f.write(f"Error splitting documents: {e}\n")
        return

    # Send to ragdoll service
    ragdoll = RagdollService()
    data = {
        "document_id": "1234567890",
        "document_type": "GoogleDrive",
        "texts": texts[0].page_content
    }
    try:
        response = ragdoll.sendChunk(data)
        with (open("output.log", "a")) as f:
            f.write(f"Response from Ragdoll: {response}\n")
        return
    except Exception as e:
        with (open("error.log", "a")) as f:
            f.write(f"Error sending to Ragdoll: {e}\n")
        return

    # with (open("output.log", "a")) as f:
    #     f.write(texts[0].page_content)
    #     f.write(f"The length is {len(texts)}.")
    # return