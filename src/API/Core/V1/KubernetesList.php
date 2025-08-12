<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\Contracts\ResourceInterface;

/**
 * Represents a Kubernetes List resource.
 *
 * List is a meta-resource that holds a collection of other Kubernetes resources.
 * It's used by the Kubernetes API to return multiple resources in a single response,
 * commonly used with list operations and watch streams.
 *
 * @see https://kubernetes.io/docs/reference/kubernetes-api/common-definitions/list-meta/
 */
class KubernetesList extends AbstractAbstractResource
{
    /** @var array<int, ResourceInterface> */
    protected array $items = [];

    /**
     * Create a list from an array representation.
     *
     * @param array<string, mixed> $data Array representation of the list
     *
     * @return static The created list instance
     */
    public static function fromArray(array $data): static
    {
        /** @var static $list */
        $list = new static();

        $list->setMetadata($data['metadata'] ?? []);

        // Note: Items array contains raw resource data that would need
        // a ResourceFactory to convert to proper resource instances.
        // For now, we store the raw array data in the spec.
        if (isset($data['items']) && is_array($data['items'])) {
            $list->spec['items'] = $data['items'];
        }

        return $list;
    }

    /**
     * Get the items in the list.
     *
     * @return array<int, ResourceInterface> Array of Kubernetes resources
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Set the items in the list.
     *
     * @param array<int, ResourceInterface> $items Array of Kubernetes resources
     *
     * @return self
     */
    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Add an item to the list.
     *
     * @param ResourceInterface $item The resource to add
     *
     * @return self
     */
    public function addItem(ResourceInterface $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Get the number of items in the list.
     *
     * @return int The count of items
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Check if the list is empty.
     *
     * @return bool True if the list has no items
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Get the first item in the list.
     *
     * @return ResourceInterface|null The first item or null if list is empty
     */
    public function first(): ?ResourceInterface
    {
        return $this->items[0] ?? null;
    }

    /**
     * Get the last item in the list.
     *
     * @return ResourceInterface|null The last item or null if list is empty
     */
    public function last(): ?ResourceInterface
    {
        return empty($this->items) ? null : $this->items[array_key_last($this->items)];
    }

    /**
     * Filter items by resource kind.
     *
     * @param string $kind The resource kind to filter by
     *
     * @return array<int, ResourceInterface> Filtered resources
     */
    public function filterByKind(string $kind): array
    {
        return array_values(array_filter($this->items, function (ResourceInterface $item) use ($kind) {
            return $item->getKind() === $kind;
        }));
    }

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind 'List'
     */
    public function getKind(): string
    {
        return 'List';
    }

    /**
     * Filter items by namespace (for namespaced resources).
     *
     * @param string $namespace The namespace to filter by
     *
     * @return array<int, ResourceInterface> Filtered resources
     */
    public function filterByNamespace(string $namespace): array
    {
        return array_values(array_filter($this->items, function (ResourceInterface $item) use ($namespace) {
            $metadata = $item->getMetadata();
            return ($metadata['namespace'] ?? null) === $namespace;
        }));
    }

    /**
     * Convert the list to an array representation.
     *
     * @return array<string, mixed> Array representation of the list
     */
    public function toArray(): array
    {
        $data = [
            'apiVersion' => $this->getApiVersion(),
            'kind'       => $this->getKind(),
            'metadata'   => $this->metadata,
            'items'      => [],
        ];

        foreach ($this->items as $item) {
            $data['items'][] = $item->toArray();
        }

        return $data;
    }
}
