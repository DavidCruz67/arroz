/**
 * Alterna a seleção entre Aluno e Professor e exibe/oculta o campo de código secreto
 */
function selecionarTipo(tipo) {
    const chkAluno = document.getElementById('chk_aluno');
    const chkProfessor = document.getElementById('chk_professor');
    const grupoCodigo = document.getElementById('grupo_codigo');
    const inputCodigo = document.getElementById('codigo_professor');

    if (tipo === 'Professor') {
        chkAluno.checked = false;
        chkProfessor.checked = true;
        grupoCodigo.style.display = 'block';
    } else {
        chkAluno.checked = true;
        chkProfessor.checked = false;
        grupoCodigo.style.display = 'none';
        inputCodigo.value = ''; // Limpa o campo do código
    }
}