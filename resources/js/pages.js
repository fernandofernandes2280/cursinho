
//FUNÇÃO PARA VALIDAR CPF
function verificarCPF(cpf) {  
    // Remove os pontos/traço da expressão regular, caso exista
    cpf = normalizarCPF(cpf);
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
    var add = 0;

    for (var i = 0; i < 9; i ++) {
        add += parseInt(cpf.charAt(i)) * (10 - i);  
    }
    var rev = 11 - (add % 11);
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

function normalizarCPF(cpf) {
    return (cpf || '').replace(/[^\d]+/g,'');
}

function normalizarTelefone(telefone) {
    return (telefone || '').replace(/[^\d]+/g, '').substring(0, 11);
}

function formatarTelefone(telefone) {
    var digitos = normalizarTelefone(telefone);

    if (digitos.length !== 11) {
        return telefone || '';
    }

    return '(' + digitos.substring(0, 2) + ')' + digitos.substring(2, 7) + '-' + digitos.substring(7);
}

function telefoneValido(telefone) {
    return /^\(\d{2}\)\d{5}-\d{4}$/.test(telefone || '');
}

function limpar_validacao(campo) {
    if(campo.next().hasClass("label-error")) {
        campo.next().remove();
    }
    campo.removeClass("item-error");
}

function exibir_validacao(campo, mensagemErro) {
    limpar_validacao(campo);
    campo.after("<div class='label-error'><span class='badge badge-sm bg-gradient-danger'>" + mensagemErro + "</span></div>");
    campo.addClass("item-error");
}

verifica_validacao = function(campo, funcaoValidacao, mensagemErro) {
    // Verifica se é um CPF
    if(campo.val() != '' && funcaoValidacao(campo.val()) == false) {
        // Se for um CPF, incluir a label do erro e marca o item com a classe de erro
        exibir_validacao(campo, mensagemErro);
        return false;
    } else {
        // Se não for um CPF, remove a classe item-error
        limpar_validacao(campo);
        return true;
    }
}

$( document ).ready(function() {

    function iniciarRascunhoDosForms() {
        var forms = document.querySelectorAll('form[method="post"]');
        var storage = null;

        try {
            storage = window.sessionStorage;
        } catch (error) {
            return;
        }

        if (!forms.length || !storage) {
            return;
        }

        Array.prototype.forEach.call(forms, function(form, index) {
            var formClass = form.className.split(/\s+/)[0] || ('form-' + index);
            var storageKey = 'cursinho:form-draft:' + window.location.pathname + window.location.search + ':' + formClass;
            var submitted = false;

            function isDraftField(field) {
                if (!field.name || field.disabled) {
                    return false;
                }

                return ['button', 'file', 'hidden', 'image', 'password', 'reset', 'submit'].indexOf(field.type) === -1;
            }

            function getFields() {
                return Array.prototype.filter.call(form.querySelectorAll('input[name], select[name], textarea[name]'), isDraftField);
            }

            function salvarRascunho() {
                if (submitted) {
                    return;
                }

                var data = {};

                getFields().forEach(function(field) {
                    if (field.type === 'checkbox') {
                        data[field.name] = field.checked ? (field.value || '1') : '';
                        return;
                    }

                    if (field.type === 'radio') {
                        if (field.checked) {
                            data[field.name] = field.value;
                        }
                        return;
                    }

                    if (field.tagName === 'SELECT' && field.multiple) {
                        data[field.name] = Array.prototype.map.call(field.selectedOptions, function(option) {
                            return option.value;
                        });
                        return;
                    }

                    data[field.name] = field.value;
                });

                try {
                    storage.setItem(storageKey, JSON.stringify(data));
                } catch (error) {
                    return;
                }
            }

            function restaurarRascunho() {
                var raw = storage.getItem(storageKey);

                if (!raw) {
                    return;
                }

                try {
                    var data = JSON.parse(raw);
                } catch (error) {
                    storage.removeItem(storageKey);
                    return;
                }

                getFields().forEach(function(field) {
                    if (!Object.prototype.hasOwnProperty.call(data, field.name)) {
                        return;
                    }

                    if (field.tagName === 'SELECT' && !Array.isArray(data[field.name])) {
                        field.setAttribute('data-form-draft-value', data[field.name]);
                    }

                    if (field.type === 'checkbox') {
                        field.checked = data[field.name] === (field.value || '1');
                        return;
                    }

                    if (field.type === 'radio') {
                        field.checked = data[field.name] === field.value;
                        return;
                    }

                    if (field.tagName === 'SELECT' && field.multiple && Array.isArray(data[field.name])) {
                        Array.prototype.forEach.call(field.options, function(option) {
                            option.selected = data[field.name].indexOf(option.value) !== -1;
                        });
                        return;
                    }

                    field.value = data[field.name];
                });

                form.dispatchEvent(new CustomEvent('formDraftRestored', {
                    detail: {
                        data: data
                    }
                }));
            }

            restaurarRascunho();

            getFields().forEach(function(field) {
                field.addEventListener('input', salvarRascunho);
                field.addEventListener('change', salvarRascunho);
            });

            form.addEventListener('submit', function(event) {
                setTimeout(function() {
                    if (event.defaultPrevented) {
                        return;
                    }

                    submitted = true;
                    storage.removeItem(storageKey);
                }, 0);
            });
        });
    }

    iniciarRascunhoDosForms();

    function iniciarToastsDoSistema() {
        var slot = document.querySelector(".navbar-toast-slot, .login-toast-slot");
        var toasts = Array.prototype.slice.call(document.querySelectorAll("[data-system-toast]"));

        if (!toasts.length) {
            return;
        }

        function fecharToast(toast, redirectUrl) {
            if (toast.dataset.toastClosing === "1") {
                return;
            }

            toast.dataset.toastClosing = "1";
            toast.classList.remove("is-visible");
            toast.classList.add("is-hiding");

            setTimeout(function() {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }

                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            }, 220);
        }

        toasts.forEach(function(toast) {
            if (slot) {
                slot.appendChild(toast);
            }

            var close = toast.querySelector(".system-toast-close");
            if (close) {
                close.addEventListener("click", function() {
                    fecharToast(toast);
                });
            }

            requestAnimationFrame(function() {
                toast.classList.add("is-visible");
            });

            setTimeout(function() {
                if (toast.parentNode) {
                    fecharToast(toast, toast.getAttribute("data-toast-redirect"));
                }
            }, 2333);
        });
    }

    iniciarToastsDoSistema();

    function iniciarMascaraTelefone() {
        var campos = $(".mascara-telefone");

        if (!campos.length) {
            return;
        }

        campos.each(function() {
            var campo = $(this);
            var valor = campo.val();

            campo.attr("maxlength", "14");

            if (!valor) {
                campo.val("(00)00000-0000");
                return;
            }

            if (!telefoneValido(valor)) {
                campo.val(formatarTelefone(valor));
            }
        });

        campos.mask("(00)00000-0000");

        campos.on("focus", function() {
            if ($(this).val() === "(00)00000-0000") {
                this.select();
            }
        });

        campos.on("input keyup paste change", function() {
            var campo = $(this);

            setTimeout(function() {
                if (!campo.val()) {
                    campo.val("(00)00000-0000");
                }

                if (telefoneValido(campo.val())) {
                    limpar_validacao(campo);
                }
            }, 0);
        });

        campos.on("blur", function() {
            var campo = $(this);

            if (telefoneValido(campo.val())) {
                limpar_validacao(campo);
                return;
            }

            exibir_validacao(campo, "Formato: (00)00000-0000");

            setTimeout(function() {
                campo.trigger("focus");
            }, 0);
        });

        campos.closest("form").on("submit", function(event) {
            var campoInvalido = $(this).find(".mascara-telefone").filter(function() {
                return !telefoneValido($(this).val());
            }).first();

            if (!campoInvalido.length) {
                return;
            }

            event.preventDefault();
            exibir_validacao(campoInvalido, "Formato: (00)00000-0000");
            campoInvalido.trigger("focus");
        });
    }

    // Definições de Máscaras
    $(".mascara-data").mask("99/99/9999");
    $(".mascara-cpf").mask("000.000.000-00");
    $(".mascara-cnpj").mask("99.999.999/9999-99");
    $(".mascara-telefone1").mask("(96)9999-99999");
    $(".mascara-cep").mask("99.999-999");
    iniciarMascaraTelefone();

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

    function validarCpfNovoAluno() {
        var campo = $("#modalNovoCpfAluno #cpfAluno:not(.no-validation)");
        var botao = $("#novoAluno");

        if (!campo.length || !botao.length) {
            return true;
        }

        var cpf = normalizarCPF(campo.val());
        var valido = cpf.length === 11 && verificarCPF(campo.val());

        botao.prop("disabled", !valido).toggleClass("disabled", !valido);

        if (cpf.length === 0) {
            limpar_validacao(campo);
            return false;
        }

        if (cpf.length < 11) {
            exibir_validacao(campo, "CPF incompleto");
            return false;
        }

        if (!valido) {
            exibir_validacao(campo, "CPF inválido");
            return false;
        }

        limpar_validacao(campo);
        return true;
    }

    var cpfNovoAluno = $("#modalNovoCpfAluno #cpfAluno:not(.no-validation)");
    if (cpfNovoAluno.length) {
        validarCpfNovoAluno();
        cpfNovoAluno.on("input keyup paste change", function() {
            setTimeout(validarCpfNovoAluno, 0);
        });
        $("#modalNovoCpfAluno form").on("submit", function(event) {
            if (!validarCpfNovoAluno()) {
                event.preventDefault();
            }
        });
    }
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
	
	

	//coloca mascará no campo matrícula
$('.matricula').bind('keyup', function (event) {
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
