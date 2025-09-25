<?php

namespace local_mxaimanager\app\ai\provider\providers;


// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

// @codeCoverageIgnoreEnd

class ollama implements \local_mxaimanager\app\ai\provider\providers\interfaces\chat_completion,
                        \local_mxaimanager\app\ai\provider\providers\interfaces\create_embedding
{
    public function chat_completion(array $messages, string $model): string
    {
        // TODO: Implement chat_completion() method.
        /*
         * https://ollama.readthedocs.io/en/api/#generate-a-chat-completion
         * Example chat completion curl request:
           curl http://dashboard:11434/api/chat -d '{
             "model": "llama3",
             "stream": false,
             "messages": [
               {
                 "role": "user",
                 "content": "why is the sky blue?"
               }
             ]
           }'
         */
    }

    public function get_embedding(string $input, string $model, int $dimension): array
    {
        // TODO: Implement get_embedding() method.
        /*
         * https://ollama.readthedocs.io/en/api/#generate-embeddings
         * Example embedding curl request:
            curl http://dashboard:11434/api/embed -d '{
              "model": "all-minilm",
              "input": "Why is the sky blue?"
            }'
         */
    }
}
