=== Paulo Cezario - RTE Rodonaves shipping for WooCommerce ===
Contributors: phscezario
Donate link: https://picpay.me/phscezario
Tags: shipping, delivery, woocommerce, rte rodonaves
Requires at least: 6.0
Tested up to: 6.2
Stable tag: 0.2.0
Requires PHP: 7.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integration between the RTE Rodonaves and WooCommerce

== Description ==

Utilize cálculo de frete RTE Rodonaves no WooCommerce.

[RTE Rodonaves](https://rte.com.br/) é uma transportadora brasileira.

Deseja contribuir com esse plugin? Visite: [GitHub](https://github.com/phscezario/wc-rte-rodonaves-shipping).

Este plugin foi desenvolvido sem nenhum incentivo da RTE Rodonaves. O desenvolvedor deste plugin não possui vínculo com esta empresa. Este plugin foi desenvolvido com base na API de integração da RTE Rodonaves.

= Funcionalidades do plugin =

Este plugin adiciona sistema de frete na página de carrinho da sua loja WooCommerce, e também adiciona simulador de frete na pagina de produto.

== Installation ==

= Instalação do plugin: =

- Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
- Ative o plugin.

= Compatibilidade =

Requer WooCommerce 3.0 ou posterior para funcionar.

= Dúvidas? =

Você pode esclarecer suas dúvidas usando:

- Utilizando o nosso [fórum no Github](https://github.com/paulocezario/wc-rte-rodonaves-shipping).

= Configurações dos produtos =

É necessário configurar o **peso** e **dimensões** de todos os seus produtos, para que a cotação de frete funcione corretamente.
É possível configurar com produtos do tipo **simples** ou **variável** e não *virtuais* (produtos virtuais são ignorados na hora de cotar o frete).  

== Frequently Asked Questions ==

= O que eu preciso para usar este plugin =

Você precisa ter cadastro junto a RTE Rodonaves e preencher corretamente as informações no plugin.

= Este plugin faz cotação direta com o sistema da RTE Rodonaves =

Não, no momento ele faz cotações e retorna os valores, para confirmar seu frete é preciso entrar em contato com a RTE Rodonaves e confirmar.

= Meu site ficou lento com esse plugin? =

As requisições à API da RTE Rodonaves podem demorar um pouco para retornar resposta, não sei se eles possuem um limite de banda de acordo com o cadastro do cliente.

= Error comuns =

Aqui uma lista de erros mais comuns:

- Faltando CEP de origem nos métodos configurados.
- CEP de origem inválido.
- Produtos cadastrados sem peso e dimensões
- Peso e dimensões cadastrados de forma incorreta (por exemplo configurando como 1000kg, pensando que seria 1000g, então verifique as configurações de medidas em `WooCommerce > Configurações > Produtos`).

E não se esqueça de verificar o erro ativando a opção de **Log de depuração** nas configurações de cada método de entrega. Imediatamente após ativar o log, basta tentar cotar o frete novamente, fazendo assim o log ser gerado. Você pode acessar todos os logs indo em "WooCommerce" > "Status do sistema" > "Logs".

Algumas vezes a API da RTE Rodonaves por ficar indisponível, assim ela pode retornar erro ou apenas não executar o cálculo.
	
== Changelog ==

= 0.2.0 =
* Este plugin agora aceita a medida de peso em kg (quilos) e g (gramas).
 
= 0.1.0 =
* Foi ajustado o erro que dava antes de iniciar o plugin, então algumas vezes mesmo configurando não retornava os dados corretos.
* Foi adicionado botão para conseguir o ID da cidade na pagina de configuração, deixando o plugin mais rápido.

== Screenshots ==

1. Pagina de configuração do plugin.
2. Exemplo na página de produto.
3. Exemplo na página de carrinho.