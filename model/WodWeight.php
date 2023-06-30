<?php

class WodWeight
{
    public int $id;
    public int $wodId;
    public int $equipmentId;

    public WeightGender $weightGender;
    public string $weightFactor;

    public int $order;
    public float $weight;
    public WeightUnit $weightUnit;

    public string $notes;
}
