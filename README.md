# Prompt Injection Lab

An intentionally vulnerable LLM chat demo for learning about prompt injection.
A local model (via LM Studio) guards a **fake** secret. Your goal is to make it leak.

> Educational use only. The secret in `fake_secret.key` is a made-up value.

## Levels
1. **Guarded Vault (Easy):** a single "never disclose" instruction.
2. **Keyword Filter (Medium):** the prompt tells the model to refuse inputs containing certain words.
3. **Adversarial Sandbox (Hard):** user input is wrapped in delimiters and treated as untrusted.

When the model's reply contains the secret, a green banner appears.

## Requirements
- PHP 8.0+ with the `curl` extension
- [LM Studio](https://lmstudio.ai/) with a model loaded and the local server running

## Setup
```bash
cp .env.example .env      # then edit the model name to match LM Studio
php -S localhost:8000
```
Open http://localhost:8000.

## Defenses (discussion)
- Never put secrets in prompts
- Least privilege for any tools or data the model can reach
- Filter output server-side, not just input
- Treat model output as untrusted