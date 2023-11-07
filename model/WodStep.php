<?php

class WodStep
{
    public int $id;
    public int $wodId;
    public int $equipmentId;
    public int $movementId;

    public WeightGender $weightGender;
    public string $weightFactor;

    public int $order;
    public float $weight;
    public WeightUnit $weightUnit;

    public string $notes;
}
