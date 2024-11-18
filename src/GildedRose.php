<?php

namespace App;

use App\Enums\ItemTypeEnum;

final class GildedRose
{
    private const SULFURAS_QUALITY = 80;

    public function updateQuality($item): void
    {
        if ($this->isSulfuras($item)) { // Najłatwiej na początek wyrzucić z logiki najprostsze rozwiązania - eliminujemy jeden łatwy element
            $this->processSulfuras($item);
            return;
        }

        $this->processQualityForStandardItems($item); // logika dla wszystkich itemów
        $this->updateSellIn($item); // zawsze aktualizujemy na - datę do sprzedania

        if ($this->isAfterExpireDate($item)) { //Sprawdzamy czy jest po terminie ważności
            $this->processExpiredItem($item);
        }
    }

    private function isSulfuras($item): bool
    {
        return $item->name === ItemTypeEnum::SULFURAS->value;
    }

    private function processSulfuras($item): void
    {
        $item->quality = self::SULFURAS_QUALITY;
        // - "**Sulfuras**", being a legendary item, never has to be sold or decreases in Quality
        // Just for clarification, an item can never have its Quality increase above 50, however "Sulfuras" is a legendary 
        // item and as such its Quality is 80 and it never alters.
    }

    private function processQualityForStandardItems($item): void
    {
        if ($this->isSelectedItem($item)) {
            $this->updateSelectedItemsQuality($item);
        } else {
            $this->decreaseQuality($item);
        }
    }

    private function updateSelectedItemsQuality($item): void
    {
        $this->increaseQuality($item); //actually increases in Quality the older it gets
        //increase quality 1
        if ($item->name === ItemTypeEnum::BACKSTAGE_PASS->value) {
            if ($item->sell_in < 11) {
                $this->increaseQuality($item);
            } //increase quality 2
            if ($item->sell_in < 6) {
                $this->increaseQuality($item);
            } //increase quality 3
        }
        // like aged brie, increases in Quality as it's
        // SellIn value approaches; Quality increases by 2 when there are 10 days or less
        // and by 3 when there are 5 days or less but Quality drops to 0 after the concert
    }

    private function isSelectedItem($item): bool
    {
        return in_array($item->name, [ItemTypeEnum::BACKSTAGE_PASS->value, ItemTypeEnum::AGED_BRIE->value]);
    }

    private function updateSellIn($item): void
    {
        $item->sell_in--;
    }

    private function increaseQuality($item,): void
    {
        if ($item->quality < 50) { // The Quality of an item is never more than 50
            $item->quality++;
        }
    }

    private function decreaseQuality($item): void
    {
        if ($item->quality > 0) { //- The Quality of an item is never negative 
            $item->quality--;
        }
    }

    private function isAfterExpireDate($item): bool
    {
        return $item->sell_in < 0 ? true : false;
    }

    private function processExpiredItem($item): void
    {
        if ($item->name === ItemTypeEnum::AGED_BRIE->value) {
            $this->increaseQuality($item); // Once the sell by date has passed, Quality degrades twice as fast //actually increases in Quality the older it gets
        } elseif ($item->name === ItemTypeEnum::BACKSTAGE_PASS->value) {
            $item->quality = 0; // Quality drops to 0 after the concert
        } else {
            $this->decreaseQuality($item); // Once the sell by date has passed, Quality degrades twice as fast
        }
    }
}
