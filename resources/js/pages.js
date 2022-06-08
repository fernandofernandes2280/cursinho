
//FUNÇÃO PARA VALIDAR CPF
function verificarCPF(cpf) {  
    // Remove os pontos/traço da expressão regular, caso exista
    cpf = cpf.replace(/[^\d]+/g,'');    
    if(cpf == '') return false;     

    // Elimina CPFs invalidos conhecidos    
    if (cpf.length != 11 ||         
    cpf == "00000000000" ||         
    cpf == "11111111111" ||         
    cpf == "22222222222" ||         
    cpf == "33333333333" ||         
    cpf == "44444444444" ||         
    cpf == "55555555555" ||         
    cpf == "66666666666" ||         
    cpf == "77777777777" ||         
    cpf == "88888888888" ||         
    cpf == "99999999999")       
    return false;         

    // Valida 1o digito 
    add = 0;        

    for (i=0; i < 9; i ++) {
        add += parseInt(cpf.charAt(i)) * (10 - i);  
    }
    rev = 11 - (add % 11);  
    if (rev == 10 || rev == 11) {
        rev = 0;
    }  

    if (rev != parseInt(cpf.charAt(9))) {
        return false;
    }   

    // Valida 2o digito 
    add = 0;    
    for (i = 0; i < 10; i ++) {
        add += parseInt(cpf.charAt(i)) * (11 - i);
    }    
    rev = 11 - (add % 11);     
    if (rev == 10 || rev == 11) {
        rev = 0;
    }    
    if (rev != parseInt(cpf.charAt(10))) {
        return false;
    }  
    return true;   
}

verifica_validacao = function(campo, funcaoValidacao, mensagemErro) {
    if(campo.next().hasClass("label-error")) {
        campo.next().remove();
    }
    // Verifica se é um CPF
    if(campo.val() != '' && funcaoValidacao(campo.val()) == false) {
        // Se for um CPF, incluir a label do erro e marca o item com a classe de erro
      campo.after("<div class='label-error'><span class='badge badge-sm bg-gradient-danger'>" + mensagemErro + "</span></div>");
     campo.addClass("item-error");



    } else {
        // Se não for um CPF, remove a classe item-error
 
        campo.removeClass("item-error");

    }
}

$( document ).ready(function() {

       // Definições de Máscaras
       $(".mascara-data").mask("99/99/9999");
       $(".mascara-cpf").mask("999.999.999-99");
       $(".mascara-cnpj").mask("99.999.999/9999-99");
       $(".mascara-telefone1").mask("(96)9999-99999");
       $(".mascara-cep").mask("99.999-999");

       $('.mascara-telefone1').focusout(function(){
        var phone, element;
        element = $(this);
        element.unmask();
        phone = element.val().replace(/\D/g, '');
        if(phone.length > 10) {
            element.mask("(99)99999-9999");
        } else {
            element.mask("(99)9999-99999");
        }
    });

	//APLICA A VALIDAÇÃO AO VIVO
	$(".cpfLogin:not(.no-validation)").change(function() {verifica_validacao($(this), verificarCPF, "CPF inválido");});
	$(".cpfPesquisa:not(.no-validation)").change(function() {verifica_validacao($(this), verificarCPF, "CPF inválido");});
	$(".cpfNovo:not(.no-validation)").change(function() {verifica_validacao($(this), verificarCPF, "CPF inválido");});
	$(".cpfAluno:not(.no-validation)").change(function() {verifica_validacao($(this), verificarCPF, "CPF inválido");});
	$(".cpfProfessor:not(.no-validation)").change(function() {verifica_validacao($(this), verificarCPF, "CPF inválido");});
	$(".cpfUser:not(.no-validation)").change(function() {verifica_validacao($(this), verificarCPF, "CPF inválido");});
});














$(document).ready(function() {
//	showAllUser();
	function showAllUser(){
		$.ajax({
			url: "/projetoMVC/app/Utils/Ajax.php",
			type: "POST",
			data: {action:"view"},
			success:function(bairros){
				console.log(bairros);
			}
		});
	}
	
	$(function(){
		//alert("a");
	});
	

	//Codigo ajax requisição MVC
	$("body").on("click", "[data-action]", function(e){
		e.preventDefault();
		var data = $(this).data();
		var div = $(this).parent();
		
		$.post(data.action,data,function(id){
			div.fadeOut();
			alert(100);
			location.reload();
			
			
		},"json").fail(function(){
			console.log("erro");
		});
	});
	
	
	//Script NAVBAR
	    $('.navbar .dropdown-item').on('click', function (e) {
        var $el = $(this).children('.dropdown-toggle');
        var $parent = $el.offsetParent(".dropdown-menu");
        $(this).parent("li").toggleClass('open');

        if (!$parent.parent().hasClass('navbar-nav')) {
            if ($parent.hasClass('show')) {
                $parent.removeClass('show');
                $el.next().removeClass('show');
                $el.next().css({"top": -999, "left": -999});
            } else {
                $parent.parent().find('.show').removeClass('show');
                $parent.addClass('show');
                $el.next().addClass('show');
                $el.next().css({"top": $el[0].offsetTop, "left": $parent.outerWidth() - 4});
            }
            e.preventDefault();
            e.stopPropagation();
        }
    });

    $('.navbar .dropdown').on('hidden.bs.dropdown', function () {
        $(this).find('li.dropdown').removeClass('show open');
        $(this).find('ul.dropdown-menu').removeClass('show open');
    });
	
/*
	
	//Evento qdo botao RAAS é clicado
	document.getElementById("btnRaas").addEventListener("click", labelRaas);

	//Função para alterar o título do Modal produção
	function labelRaas() {
	  document.getElementById("modalLabel").innerHTML = "Relatório RAAS";
	  document.getElementById("instrumento").value = "3";
	}	
	//Evento qdo botao BPAC é clicado
	document.getElementById("btnBpac").addEventListener("click", labelBpac);

	//Função para alterar o título do Modal produção
	function labelBpac() {
	  document.getElementById("modalLabel").innerHTML = "Relatório BPA-C";
	document.getElementById("instrumento").value = "2";
	}
	
	//Evento qdo botao BPAI é clicado
	document.getElementById("btnBpai").addEventListener("click", labelBpai);

	//Função para alterar o título do Modal produção
	function labelBpai() {
	  document.getElementById("modalLabel").innerHTML = "Relatório BPA-I";
	document.getElementById("instrumento").value = "1";
	}
	*/
/////Máscaras para Telefone //////////////////	

	$("#fone1").blur(function() {
		var fone = $(this).val();
		
		function mascara(o,f){
		    v_obj=o
		    v_fun=f
		    setTimeout("execmascara()",1)
		}
		function execmascara(){
		    v_obj.value=v_fun(v_obj.value)
		}
		function mtel(v){
		    v=v.replace(/\D/g,"");             //Remove tudo o que não é dígito
		//    v=v.replace(/^(\d{2})(\d)/g,"(96) $1$2"); //Coloca (96) no início do número
		    v=v.replace(/(\d)(\d{4})$/,"$1-$2");    //Coloca hífen entre o quarto e o quinto dígitos
		    return v;
		}
		 		
		$(this).val(mtel(fone));
	
	 });
	
	$("#matricula1").blur(function() {
		var fone = $(this).val();
		
	
		function mascara(o,f){
		    v_obj=o
		    v_fun=f
		    setTimeout("execmascara()",1)
		}
		function execmascara(){
		    v_obj.value=v_fun(v_obj.value)
		}
		function mtel(v){
		    v=v.replace(/\D/g,"");             //Remove tudo o que não é dígito
		//    v=v.replace(/^(\d{2})(\d)/g,"(96) $1$2"); //Coloca (96) no início do número
		    v=v.replace(/(\d)(\d{4})$/,"$1-$2");    //Coloca hífen entre o quarto e o quinto dígitos
		    return v;
		}
		$(this).val(mtel(fone));
	
	 });
	
	//coloca mascará no campo matrícula
$('#matricula').bind('keyup', function (event) {
            if (!(event.which > 47 && event.which < 58)) {
                  
		var conteudo = $(this).val();
		
	
		function mascara(o,f){
		    v_obj=o
		    v_fun=f
		    setTimeout("execmascara()",1)
		}
		function execmascara(){
		    v_obj.value=v_fun(v_obj.value)
		}
		function mtel(v){
		    v=v.replace(/\D/g,"");             //Remove tudo o que não é dígito
		//    v=v.replace(/^(\d{2})(\d)/g,"(96) $1$2"); //Coloca (96) no início do número
		    v=v.replace(/(\d)(\d{1})$/,"$1-$2");    //Coloca hífen entre o quarto e o quinto dígitos
		    return v;
		}
		$(this).val(mtel(conteudo));

                }
            });
	
	

//Busca o CEP via WEBSErvice
            function limpa_formulário_cep() {
                // Limpa valores do formulário de cep.
                $("#rua").val("");
                $("#bairro").val("");
                $("#cidade").val("");
                $("#uf").val("");
                $("#ibge").val("");
            }
            
            //Quando o campo cep perde o foco.
            $("#cep").blur(function() {

                //Nova variável "cep" somente com dígitos.
                var cep = $(this).val().replace(/\D/g, '');

                //Verifica se campo cep possui valor informado.
                if (cep != "") {

                    //Expressão regular para validar o CEP.
                    var validacep = /^[0-9]{8}$/;

                    //Valida o formato do CEP.
                    if(validacep.test(cep)) {

                        //Preenche os campos com "..." enquanto consulta webservice.
                        $("#rua").val("...");
                        $("#bairro").val("...");
                        $("#cidade").val("...");
                        $("#uf").val("...");
                        $("#ibge").val("...");

                        //Consulta o webservice viacep.com.br/
                        $.getJSON("https://viacep.com.br/ws/"+ cep +"/json/?callback=?", function(dados) {

                            if (!("erro" in dados)) {
                                //Atualiza os campos com os valores da consulta.
                                $("#endereco").val(dados.logradouro);
								$("#bairro").val(dados.bairro);
                                $("#cidade").val(dados.localidade);
                                $("#uf").val(dados.uf);

                            } //end if.
                            else {
                                //CEP pesquisado não foi encontrado.
                            //xx    limpa_formulário_cep();
                                alert("CEP não encontrado.");
                            }
                        });
                    } //end if.
                    else {
                        //cep é inválido.
                      //xx  limpa_formulário_cep();
                        alert("Formato de CEP inválido.");
                    }
                } //end if.
                else {
                    //cep sem valor, limpa formulário.
                  //xx  limpa_formulário_cep();
                }
            });
        });










