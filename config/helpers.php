<?php
/**
 * Funções de apoio usadas pelas páginas.
 */

/**
 * Escapa um valor para exibir com segurança em HTML (evita XSS).
 * Use em toda saída vinda do banco ou do usuário.
 *
 * @param mixed $valor Valor a exibir; é convertido para string.
 */
function h($valor): string
{
    return htmlspecialchars((string) $valor);
}

/**
 * Confere se a string é uma data real no formato Y-m-d.
 * Rejeita datas que não existem no calendário, como 2026-02-31.
 *
 * @param string $data Data a validar.
 */
function dataValida(string $data): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d && $d->format('Y-m-d') === $data;
}
