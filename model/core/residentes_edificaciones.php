<?php
/*
 * Copyright (C) 2016 Joe Nilson <joenilson at gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\model;
/**
 * Description of residentes_edificaciones
 *
 * @author Joe Nilson <joenilson at gmail.com>
 */
class residentes_edificaciones extends \fs_model{
    /**
     * El Id de la de edificacion
     * @var type integer serial
     */
    public $id;
    /**
     * De cara al usuario se mostrará y buscara este código
     * pero esto se generará de los valores que ingrese de
     * los tipos de edificaciones
     * @var type string
     */
    public $codigo;
    /**
     * Este codigo es en realidad una cadena para saber donde buscar
     * los datos de las edificaciones, si solo manejan el tipo de edificación
     * principal que es Bloque con código 1, entonces el codigo interno
     * será 1:LETRA_O_NUMERO del Bloque, si por el contrario tiene Bloque y Edificio
     * entonces el valor guardado será 1:1_2:1 que indicara que esta en el Bloque 1 Edificio 1
     * siendo el id de Edificio el 2.
     * @var type string
     */
    public $codigo_interno;
    /**
     * Este es el número del Inmueble
     * @var type string
     */
    public $numero;
    /**
     * @todo En un futuro si se necesita colocar la ubicación del edificio, como la calle o avenida interna
     * @var type string
     */
    public $ubicacion;
    /**
     * Si la edificación esta ocupada entonces se coloca aquí el código del Residente o Cliente
     * @var type string
     */
    public $codcliente;
    /**
     * Con esto sabremos si un edificio está o no ocupado en el listado de edificios
     * @var type boolean
     */
    public $ocupado;
    public function __construct($t = FALSE) {
        parent::__construct('residentes_edificaciones','plugins/residentes');
        if($t){
            $this->id = $t['id'];
            $this->codigo = $t['codigo'];
            $this->codigo_interno = $t['codigo_interno'];
            $this->numero = $t['numero'];
            $this->ubicacion = $t['ubicacion'];
            $this->codcliente = $t['codcliente'];
            $this->ocupado = $this->str2bool($t['ocupado']);
        }else{
            $this->id = null;
            $this->codigo = null;
            $this->codigo_interno = null;
            $this->numero = null;
            $this->ubicacion = null;
            $this->codcliente = null;
            $this->ocupado = null;
        }
    }

    public function install(){
        return "";
    }

    public function all(){
        $sql = "SELECT * FROM ".$this->table_name." ORDER BY codigo_interno";
        $data = $this->db->select($sql);
        if($data){
            $lista = array();
            foreach($data as $d){
                $item = new residentes_edificaciones($d);
                $item->pertenencia = $this->pertenencia($item);
                $lista[] = $item;
            }
            return $lista;
        }else{
            return false;
        }
    }

    public function get($id){
        $sql = "SELECT * FROM ".$this->table_name." WHERE id = ".$this->intval($id).";";
        $data = $this->db->select($sql);
        if($data){
            return new residentes_edificaciones($data[0]);
        }else{
            return false;
        }
    }

    /**
     * //Si queremos buscar por codigo o codigo_interno o codcliente o numero
     * @param type $field string
     * @param type $value string
     * @return boolean|\FacturaScripts\model\residentes_edificaciones
     */
    public function get_by_field($field,$value){
        $query = (is_string($value))?$this->var2str($value):$this->intval($value);
        $sql = "SELECT * FROM ".$this->table_name." WHERE ".strtoupper(trim($field))." = ".$query.";";
        $data = $this->db->select($sql);
        if($data){
            return new residentes_edificaciones($data[0]);
        }else{
            return false;
        }
    }

    public function exists() {
        if(is_null($this->id)){
            return false;
        }else{
            return $this->get($this->id);
        }
    }

    public function save() {
        if($this->exists()){
            $sql = "UPDATE ".$this->table_name." SET ".
                    "codigo = ".$this->var2str($this->codigo).", ".
                    "codigo_interno = ".$this->var2str($this->codigo_interno)." ".
                    "numero = ".$this->var2str($this->numero)." ".
                    "ubicacion = ".$this->var2str($this->ubicacion)." ".
                    "codcliente = ".$this->var2str($this->codlciente)." ".
                    "ocupado = ".$this->var2str($this->ocupado)." ".
                    "WHERE id = ".$this->intval($this->id).";";
            return $this->db->exec($sql);
        }else{
            $sql = "INSERT INTO ".$this->table_name." (codigo, codigo_interno, numero, ubicacion, codcliente, ocupado) VALUES (".
                    $this->var2str($this->codigo).", ".
                    $this->var2str($this->codigo_interno).", ".
                    $this->var2str($this->numero).", ".
                    $this->var2str($this->ubicacion).", ".
                    $this->var2str($this->codcliente).", ".
                    $this->var2str($this->ocupado).");";
            if($this->db->exec($sql)){
                return $this->db->lastval();
            }else{
                return false;
            }
        }
    }

    public function delete() {
        $sql = "DELETE FROM ".$this->table_name." WHERE id = ".$this->intval($this->id).";";
        return $this->db->exec($sql);
    }

    private function pertenencia($d){
        $codigo_interno = $d->codigo_interno;
        $piezas = explode("_", $codigo_interno);
        $lista_ids = array();
        foreach($piezas as $pieza){
            $data = explode(":", $pieza);
            $lista_ids[$data[0]]=$data[1];
        }
        return $lista_ids;
    }

}
